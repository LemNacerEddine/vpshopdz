<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreDomain;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class DomainController extends Controller
{
    /**
     * Get store domains
     * @route GET /api/v1/dashboard/domains
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $domains = StoreDomain::where('store_id', $store->id)
            ->orderBy('is_primary', 'desc')
            ->get()
            ->map(function ($domain) {
                return [
                    'id' => $domain->id,
                    'domain' => $domain->domain,
                    'type' => $domain->type,
                    'is_primary' => $domain->is_primary,
                    'ssl_status' => $domain->ssl_status,
                    'dns_status' => $domain->dns_status,
                    'is_verified' => $domain->is_verified,
                    'verified_at' => $domain->verified_at?->format('Y-m-d H:i'),
                    'created_at' => $domain->created_at->format('Y-m-d'),
                ];
            });

        // Get subdomain
        $subdomain = $store->slug . '.' . config('app.domain', 'vpshopdz.com');

        return response()->json([
            'success' => true,
            'data' => [
                'subdomain' => $subdomain,
                'custom_domains' => $domains,
                'can_add_domain' => $store->canUseFeature('custom_domain'),
            ],
        ]);
    }

    /**
     * Add custom domain
     * @route POST /api/v1/dashboard/domains
     */
    public function store(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        // Check feature access
        if (!$store->canUseFeature('custom_domain')) {
            return response()->json([
                'success' => false,
                'message' => 'النطاقات المخصصة غير متوفرة في خطتك الحالية',
                'upgrade_required' => true,
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9][a-zA-Z0-9-_.]+\.[a-zA-Z]{2,}$/',
                'unique:store_domains,domain',
            ],
        ], [
            'domain.required' => 'النطاق مطلوب',
            'domain.regex' => 'صيغة النطاق غير صحيحة',
            'domain.unique' => 'هذا النطاق مستخدم بالفعل',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check domains limit
        $currentCount = StoreDomain::where('store_id', $store->id)->count();
        $plan = $store->plan;
        $limit = $plan->domains_limit ?? 1;

        if ($limit > 0 && $currentCount >= $limit) {
            return response()->json([
                'success' => false,
                'message' => "وصلت للحد الأقصى من النطاقات ({$limit})",
            ], 403);
        }

        $domain = StoreDomain::create([
            'store_id' => $store->id,
            'domain' => strtolower($request->domain),
            'type' => 'custom',
            'is_primary' => $currentCount === 0,
            'ssl_status' => 'pending',
            'dns_status' => 'pending',
            'is_verified' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة النطاق. يرجى إعداد DNS.',
            'data' => [
                'domain' => $domain,
                'dns_instructions' => [
                    [
                        'type' => 'CNAME',
                        'name' => $request->domain,
                        'value' => 'stores.' . config('app.domain', 'vpshopdz.com'),
                        'ttl' => 3600,
                    ],
                    [
                        'type' => 'TXT',
                        'name' => '_vpshopdz.' . $request->domain,
                        'value' => 'store-verify=' . $store->id,
                        'ttl' => 3600,
                    ],
                ],
            ],
        ], 201);
    }

    /**
     * Verify domain DNS
     * @route PUT /api/v1/dashboard/domains/{id}/verify
     */
    public function verify(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $domain = StoreDomain::where('store_id', $store->id)
            ->where('id', $id)
            ->first();

        if (!$domain) {
            return response()->json(['success' => false, 'message' => 'النطاق غير موجود'], 404);
        }

        // Check DNS records
        $cnameValid = $this->checkCNAME($domain->domain);
        $txtValid = $this->checkTXT($domain->domain, $store->id);

        if ($cnameValid && $txtValid) {
            $domain->update([
                'is_verified' => true,
                'verified_at' => now(),
                'dns_status' => 'active',
                'ssl_status' => 'provisioning',
            ]);

            // TODO: Provision SSL certificate (Let's Encrypt)

            return response()->json([
                'success' => true,
                'message' => 'تم التحقق من النطاق بنجاح! جاري إعداد شهادة SSL.',
                'data' => $domain->fresh(),
            ]);
        }

        $errors = [];
        if (!$cnameValid) $errors[] = 'سجل CNAME غير صحيح أو لم يتم نشره بعد';
        if (!$txtValid) $errors[] = 'سجل TXT للتحقق غير صحيح أو لم يتم نشره بعد';

        return response()->json([
            'success' => false,
            'message' => 'فشل التحقق من DNS',
            'errors' => $errors,
            'hint' => 'قد يستغرق نشر DNS حتى 48 ساعة. حاول مرة أخرى لاحقاً.',
        ], 400);
    }

    /**
     * Set primary domain
     * @route PUT /api/v1/dashboard/domains/{id}/primary
     */
    public function setPrimary(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $domain = StoreDomain::where('store_id', $store->id)
            ->where('id', $id)
            ->where('is_verified', true)
            ->first();

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'النطاق غير موجود أو غير مُتحقق منه',
            ], 404);
        }

        // Remove primary from all
        StoreDomain::where('store_id', $store->id)->update(['is_primary' => false]);

        // Set this as primary
        $domain->update(['is_primary' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعيين النطاق الرئيسي',
        ]);
    }

    /**
     * Delete domain
     * @route DELETE /api/v1/dashboard/domains/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $domain = StoreDomain::where('store_id', $store->id)
            ->where('id', $id)
            ->first();

        if (!$domain) {
            return response()->json(['success' => false, 'message' => 'النطاق غير موجود'], 404);
        }

        if ($domain->is_primary) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف النطاق الرئيسي. قم بتعيين نطاق آخر كرئيسي أولاً.',
            ], 400);
        }

        $domain->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف النطاق',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function checkCNAME(string $domain): bool
    {
        try {
            $records = dns_get_record($domain, DNS_CNAME);
            $expectedTarget = 'stores.' . config('app.domain', 'vpshopdz.com');

            foreach ($records as $record) {
                if (isset($record['target']) && str_contains($record['target'], $expectedTarget)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // DNS lookup failed
        }

        return false;
    }

    private function checkTXT(string $domain, string $storeId): bool
    {
        try {
            $records = dns_get_record('_vpshopdz.' . $domain, DNS_TXT);
            $expectedValue = 'store-verify=' . $storeId;

            foreach ($records as $record) {
                if (isset($record['txt']) && $record['txt'] === $expectedValue) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // DNS lookup failed
        }

        return false;
    }
}
