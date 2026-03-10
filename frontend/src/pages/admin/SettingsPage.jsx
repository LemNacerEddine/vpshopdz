import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useLocation, Link } from 'react-router-dom';
import { useLanguage } from '@/contexts/LanguageContext';
import { useStoreSettings } from '@/contexts/StoreSettingsContext';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Save,
  Store,
  CreditCard,
  Users,
  Bell,
  Shield,
  Globe,
  Loader2,
  MessageCircle,
  Send,
  CheckCircle,
  AlertCircle,
  Clock,
  Smartphone,
  BarChart,
  Megaphone,
  ArrowRight,
  ArrowLeft,
  Bold,
  Italic,
  Strikethrough,
  Smile,
  Tag,
  Gift,
  Truck,
  Percent,
  Upload,
  Image,
  Building2,
  FileText,
} from 'lucide-react';
import { toast } from 'sonner';
import WhatsAppPhonePreview from '@/components/admin/WhatsAppPhonePreview';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const SettingsPage = () => {
  const { language, isRTL } = useLanguage();
  const { refreshSettings } = useStoreSettings();
  const location = useLocation();
  const pathParts = location.pathname.split('/');
  const activeSection = pathParts[3] || '';

  const [saving, setSaving] = useState(false);
  const [loadingWhatsApp, setLoadingWhatsApp] = useState(true);
  const [loadingStore, setLoadingStore] = useState(true);
  const [uploadingLogo, setUploadingLogo] = useState(false);
  const [testingWhatsApp, setTestingWhatsApp] = useState(false);
  const [testPhone, setTestPhone] = useState('');
  const [validatingFb, setValidatingFb] = useState(false);

  // Store Settings
  const [storeSettings, setStoreSettings] = useState({
    store_name: '',
    store_email: '',
    store_phone: '',
    store_address: '',
    store_currency: 'DZD',
    store_language: 'ar',
    store_description: '',
    store_logo: '',
    store_rc: '',
    store_nif: '',
    store_nis: '',
    store_ai: '',
    store_facebook: '',
    store_instagram: '',
    store_website: '',
  });

  // Tax Settings
  const [taxSettings, setTaxSettings] = useState({
    enableTax: false,
    taxRate: 19,
    taxIncludedInPrice: true
  });

  // Notification Settings
  const [notificationSettings, setNotificationSettings] = useState({
    emailOnNewOrder: true,
    emailOnLowStock: true,
    smsOnNewOrder: false,
    lowStockThreshold: 10
  });

  // WhatsApp Settings
  const [whatsappSettings, setWhatsappSettings] = useState({
    whatsapp_enabled: false,
    whatsapp_mode: 'green_api',
    whatsapp_phone_number_id: '',
    whatsapp_access_token: '',
    green_api_instance_id: '',
    green_api_token: '',
    whatsapp_rate_limit_seconds: '120',
    whatsapp_auto_send: false,
    whatsapp_delay_minutes: '30',
    whatsapp_max_retries: '5',
    whatsapp_max_per_run: '10',
    whatsapp_phone_cooldown_minutes: '1440',
    whatsapp_send_window_start: '9',
    whatsapp_send_window_end: '21',
    whatsapp_message_ar: '',
    whatsapp_message_fr: '',
    whatsapp_message_en: '',
    store_url: '',
    fb_pixel_id: '',
    fb_app_id: '',
    fb_app_secret: '',
    fb_access_token: '',
    fb_ad_account_id: '',
    fb_page_id: '',
    // Offer settings
    offer_discount_enabled: false,
    offer_discount_type: 'percentage',
    offer_discount_value: '10',
    offer_free_shipping: false,
  });

  const [messageLang, setMessageLang] = useState(language);

  const l = {
    ar: {
      settings: 'الإعدادات',
      settingsDesc: 'إدارة جميع إعدادات المتجر',
      storeSettings: 'إعدادات المتجر',
      storeDesc: 'اسم المتجر، البريد، الهاتف، العنوان',
      taxes: 'الضرائب',
      taxesDesc: 'نسبة الضريبة وإعداداتها',
      notifications: 'الإشعارات',
      notificationsDesc: 'إشعارات البريد والرسائل',
      users: 'المستخدمين',
      usersDesc: 'إدارة صلاحيات المستخدمين',
      whatsapp: 'التسويق',
      marketingDesc: 'Facebook Pixel، واتساب، الإعلانات',
      save: 'حفظ التغييرات',
      saving: 'جاري الحفظ...',
      saved: 'تم حفظ الإعدادات',
      back: 'العودة للإعدادات',
      // Facebook Pixel
      fbPixelTitle: 'Facebook Pixel',
      fbPixelDesc: 'ربط Facebook Pixel لتتبع الأحداث وتحسين الإعلانات',
      fbPixelId: 'معرف Pixel',
      fbPixelIdDesc: 'معرف Facebook Pixel من مدير الإعلانات (مثال: 123456789012345)',
      fbPixelIdPlaceholder: 'أدخل معرف Pixel',

      // Store
      storeName: 'اسم المتجر',
      storeEmail: 'البريد الإلكتروني',
      storePhone: 'رقم الهاتف',
      storeAddress: 'العنوان',
      storeCurrency: 'العملة',
      storeLanguage: 'اللغة الافتراضية',
      storeDescription: 'وصف المتجر',
      storeLogo: 'شعار المتجر',
      storeLogoDesc: 'ارفع شعار المتجر (يظهر في الهيدر والفواتير)',
      uploadLogo: 'رفع الشعار',
      changeLogo: 'تغيير الشعار',
      businessInfo: 'المعلومات التجارية',
      businessInfoDesc: 'معلومات السجل التجاري والأرقام الجبائية (تظهر في الفواتير)',
      storeRC: 'رقم السجل التجاري (RC)',
      storeNIF: 'رقم التعريف الجبائي (NIF)',
      storeNIS: 'رقم التعريف الإحصائي (NIS)',
      storeAI: 'رقم المادة (AI)',
      socialMedia: 'مواقع التواصل الاجتماعي',
      storeFacebook: 'رابط فيسبوك',
      storeInstagram: 'رابط إنستغرام',
      storeWebsite: 'الموقع الإلكتروني',

      // Tax
      enableTax: 'تفعيل الضرائب',
      taxRate: 'نسبة الضريبة (%)',
      taxIncluded: 'الضريبة مشمولة في السعر',

      // Notifications
      emailOnNewOrder: 'إشعار بريدي عند طلب جديد',
      emailOnLowStock: 'إشعار بريدي عند انخفاض المخزون',
      smsOnNewOrder: 'إشعار SMS عند طلب جديد',
      lowStockThreshold: 'حد المخزون المنخفض',

      // Users
      userRole: 'دور المستخدم',
      admin: 'مدير',
      staff: 'موظف',
      adminDesc: 'صلاحيات كاملة',
      staffDesc: 'إدارة الطلبات والمنتجات فقط',

      // WhatsApp
      whatsappTitle: 'إعدادات واتساب',
      whatsappDesc: 'ربط واتساب لإرسال رسائل استرداد الطلبات المتروكة تلقائياً',
      enableWhatsApp: 'تفعيل واتساب',
      whatsappMode: 'وضع الإرسال',
      modeGreenApi: 'واتساب شخصي (مجاني)',
      modeGreenApiDesc: 'استخدم رقم واتساب الشخصي عبر Green API - مجاني للاستخدام المحدود',
      modeBusinessApi: 'واتساب للأعمال (مدفوع)',
      modeBusinessApiDesc: 'استخدم Meta WhatsApp Business API - للاستخدام المكثف',
      greenApiInstanceId: 'معرف المثيل (Instance ID)',
      greenApiInstanceIdDesc: 'معرف المثيل من حسابك في green-api.com',
      greenApiToken: 'رمز API (Token)',
      greenApiTokenDesc: 'رمز الوصول من حسابك في green-api.com',
      rateLimitSeconds: 'فترة الانتظار بين الرسائل (بالثواني)',
      rateLimitDesc: 'لحماية حسابك من الحظر - ننصح بـ 120 ثانية على الأقل',
      phoneNumberId: 'معرف رقم الهاتف (Phone Number ID)',
      phoneNumberIdDesc: 'من إعدادات تطبيق واتساب في Meta Business',
      accessToken: 'رمز الوصول (Access Token)',
      accessTokenDesc: 'رمز الوصول الدائم من Meta Business',
      autoSendTitle: 'الإرسال التلقائي',
      autoSendDesc: 'إرسال رسائل واتساب تلقائياً للعملاء الذين لم يكملوا طلباتهم',
      enableAutoSend: 'تفعيل الإرسال التلقائي',
      delayMinutes: 'مدة الانتظار قبل الإرسال',
      delayMinutesDesc: 'الوقت الذي ينتظره النظام بعد ترك العميل لصفحة الدفع',
      maxRetries: 'عدد المحاولات القصوى',
      maxRetriesDesc: 'عدد مرات إعادة المحاولة عند فشل الإرسال',
      maxPerRun: 'أقصى عدد رسائل لكل تشغيل',
      maxPerRunDesc: 'عدد الرسائل التي يتم إرسالها في كل مرة يعمل فيها النظام',
      phoneCooldown: 'فترة التهدئة لكل رقم',
      phoneCooldownDesc: 'لا يتم إرسال أكثر من رسالة واحدة لنفس الرقم خلال هذه الفترة',
      sendWindowStart: 'بداية وقت الإرسال',
      sendWindowEnd: 'نهاية وقت الإرسال',
      sendWindowDesc: 'يتم إرسال الرسائل فقط خلال هذه الفترة (ساعات العمل)',
      cronSetupTitle: 'إعداد التشغيل التلقائي (cPanel)',
      cronSetupDesc: 'أضف هذا الأمر في "المهام المجدولة" (Cron Jobs) في لوحة تحكم cPanel',
      cronCommand: 'أمر Cron',
      cronInterval: 'كل دقيقتين (*/2 * * * *)',
      cronCopied: 'تم نسخ الأمر!',
      messageTemplates: 'قوالب الرسائل',
      messageAr: 'الرسالة بالعربية',
      messageFr: 'الرسالة بالفرنسية',
      messageEn: 'الرسالة بالإنجليزية',
      messageVars: 'المتغيرات المتاحة: {name} = اسم العميل، {link} = رابط المتجر',
      storeUrl: 'رابط المتجر',
      storeUrlDesc: 'الرابط الكامل للمتجر (مثال: https://agroyousfi.dz)',
      testWhatsApp: 'إرسال رسالة تجريبية',
      testPhone: 'رقم الهاتف للاختبار',
      testSending: 'جاري الإرسال...',
      testSuccess: 'تم إرسال الرسالة التجريبية بنجاح!',
      testFailed: 'فشل الإرسال',
      apiSetupTitle: 'كيفية الإعداد',
      greenApiSetupSteps: [
        'سجل حساب مجاني في green-api.com',
        'أنشئ مثيل (Instance) جديد',
        'امسح رمز QR بهاتفك لربط واتساب',
        'انسخ Instance ID و API Token',
        'الصق القيم هنا واحفظ الإعدادات',
        'أضف أمر cron للتشغيل التلقائي',
      ],
      businessApiSetupSteps: [
        'انتقل إلى Meta Business Suite (business.facebook.com)',
        'أنشئ تطبيق واتساب للأعمال (WhatsApp Business App)',
        'من إعدادات API، انسخ "Phone Number ID" و"Access Token"',
        'الصق القيم هنا واحفظ الإعدادات',
        'أضف أمر cron للتشغيل التلقائي',
      ],
      warningBan: 'تحذير: الإرسال المكثف قد يؤدي لحظر حسابك. ننصح بفترة انتظار 120 ثانية على الأقل.',
      // Facebook Marketing API
      fbMarketingTitle: 'Facebook Marketing API',
      fbMarketingDesc: 'إعدادات API لإنشاء إعلانات فيسبوك تلقائياً من المنتجات',
      fbAppId: 'معرف التطبيق (App ID)',
      fbAppSecret: 'مفتاح التطبيق (App Secret)',
      fbAccessTokenAds: 'رمز الوصول (Access Token)',
      fbAccessTokenAdsDesc: 'رمز وصول System User طويل الأمد من Meta Business Manager',
      fbAdAccountId: 'معرف حساب الإعلانات (Ad Account ID)',
      fbAdAccountIdDesc: 'معرف الحساب بدون البادئة act_ (مثال: 123456789)',
      fbPageId: 'معرف صفحة فيسبوك (Page ID)',
      fbPageIdDesc: 'معرف الصفحة التي ستنشر منها الإعلانات',
      validateCredentials: 'اختبار الاتصال',
      validating: 'جاري الاختبار...',
      validationSuccess: 'تم الاتصال بنجاح!',
      validationFailed: 'فشل الاتصال',
      // Offers
      offersTitle: 'العروض',
      offersDesc: 'قدم خصماً أو شحناً مجانياً لتحفيز العميل على إكمال الطلب',
      discountType: 'نوع الخصم',
      percentage: 'نسبة مئوية',
      fixedAmount: 'مبلغ ثابت',
      discountValue: 'قيمة الخصم',
      freeShipping: 'شحن مجاني',
      freeShippingDesc: 'تقديم شحن مجاني مع رسالة الاسترداد',
      enableDiscount: 'تفعيل الخصم',
      // Rich editor
      insertVariable: 'إدراج متغير',
      varAbandoned: 'رقم الطلب',
      varDate: 'تاريخ الإنشاء',
      varTime: 'وقت الإنشاء',
      varRecover: 'رابط الاسترداد',
      varName: 'اسم العميل',
      varTotal: 'المبلغ الإجمالي',
      varNewTotal: 'المبلغ بعد الخصم',
      varDiscount: 'وصف الخصم',
      varItems: 'عدد المنتجات',
      editorLang: 'لغة الرسالة',
    },
    fr: {
      settings: 'Paramètres',
      settingsDesc: 'Gérer tous les paramètres du magasin',
      storeSettings: 'Paramètres du magasin',
      storeDesc: 'Nom, email, téléphone, adresse',
      taxes: 'Taxes',
      taxesDesc: 'Taux de taxe et configuration',
      notifications: 'Notifications',
      notificationsDesc: 'Notifications par email et messages',
      users: 'Utilisateurs',
      usersDesc: 'Gestion des permissions utilisateurs',
      whatsapp: 'Marketing',
      marketingDesc: 'Facebook Pixel, WhatsApp, publicités',
      save: 'Enregistrer',
      saving: 'Enregistrement...',
      saved: 'Paramètres enregistrés',
      back: 'Retour aux paramètres',
      fbPixelTitle: 'Facebook Pixel',
      fbPixelDesc: 'Connectez Facebook Pixel pour suivre les événements et optimiser les publicités',
      fbPixelId: 'Pixel ID',
      fbPixelIdDesc: 'ID du Facebook Pixel depuis le gestionnaire de publicités (ex: 123456789012345)',
      fbPixelIdPlaceholder: 'Entrez le Pixel ID',
      storeName: 'Nom du magasin',
      storeEmail: 'Email',
      storePhone: 'Téléphone',
      storeAddress: 'Adresse',
      storeCurrency: 'Devise',
      storeLanguage: 'Langue par défaut',
      storeDescription: 'Description',
      storeLogo: 'Logo du magasin',
      storeLogoDesc: 'Téléchargez le logo du magasin (affiché dans l\'en-tête et les factures)',
      uploadLogo: 'Télécharger le logo',
      changeLogo: 'Changer le logo',
      businessInfo: 'Informations commerciales',
      businessInfoDesc: 'Numéro de registre de commerce et numéros fiscaux (affichés sur les factures)',
      storeRC: 'Registre de Commerce (RC)',
      storeNIF: 'Numéro d\'Identification Fiscale (NIF)',
      storeNIS: 'Numéro d\'Identification Statistique (NIS)',
      storeAI: 'Article d\'Imposition (AI)',
      socialMedia: 'Réseaux sociaux',
      storeFacebook: 'Lien Facebook',
      storeInstagram: 'Lien Instagram',
      storeWebsite: 'Site web',
      enableTax: 'Activer les taxes',
      taxRate: 'Taux de taxe (%)',
      taxIncluded: 'Taxe incluse dans le prix',
      emailOnNewOrder: 'Email pour nouvelle commande',
      emailOnLowStock: 'Email pour stock faible',
      smsOnNewOrder: 'SMS pour nouvelle commande',
      lowStockThreshold: 'Seuil de stock faible',
      userRole: "Rôle d'utilisateur",
      admin: 'Admin',
      staff: 'Staff',
      adminDesc: 'Accès complet',
      staffDesc: 'Commandes et produits uniquement',

      whatsappTitle: 'Paramètres WhatsApp',
      whatsappDesc: 'Connectez WhatsApp pour envoyer automatiquement des messages de récupération de paniers abandonnés',
      enableWhatsApp: 'Activer WhatsApp',
      whatsappMode: 'Mode d\'envoi',
      modeGreenApi: 'WhatsApp personnel (gratuit)',
      modeGreenApiDesc: 'Utilisez votre numéro WhatsApp personnel via Green API - gratuit pour un usage limité',
      modeBusinessApi: 'WhatsApp Business (payant)',
      modeBusinessApiDesc: 'Utilisez Meta WhatsApp Business API - pour un usage intensif',
      greenApiInstanceId: 'Instance ID',
      greenApiInstanceIdDesc: 'ID de l\'instance depuis votre compte green-api.com',
      greenApiToken: 'API Token',
      greenApiTokenDesc: 'Token d\'accès depuis votre compte green-api.com',
      rateLimitSeconds: 'Délai entre les messages (secondes)',
      rateLimitDesc: 'Pour protéger votre compte - minimum 120 secondes recommandé',
      phoneNumberId: 'Phone Number ID',
      phoneNumberIdDesc: 'Depuis les paramètres de l\'app WhatsApp dans Meta Business',
      accessToken: 'Access Token',
      accessTokenDesc: 'Token d\'accès permanent de Meta Business',
      autoSendTitle: 'Envoi automatique',
      autoSendDesc: 'Envoyer automatiquement des messages WhatsApp aux clients qui n\'ont pas finalisé leur commande',
      enableAutoSend: 'Activer l\'envoi automatique',
      delayMinutes: 'Délai avant envoi',
      delayMinutesDesc: 'Temps d\'attente après l\'abandon du panier',
      maxRetries: 'Nombre max de tentatives',
      maxRetriesDesc: 'Nombre de tentatives en cas d\'échec d\'envoi',
      maxPerRun: 'Max messages par exécution',
      maxPerRunDesc: 'Nombre de messages envoyés à chaque exécution du système',
      phoneCooldown: 'Période de refroidissement par numéro',
      phoneCooldownDesc: 'Un seul message par numéro durant cette période',
      sendWindowStart: 'Début de la fenêtre d\'envoi',
      sendWindowEnd: 'Fin de la fenêtre d\'envoi',
      sendWindowDesc: 'Les messages ne sont envoyés que pendant cette plage horaire',
      cronSetupTitle: 'Configuration automatique (cPanel)',
      cronSetupDesc: 'Ajoutez cette commande dans "Tâches planifiées" (Cron Jobs) de cPanel',
      cronCommand: 'Commande Cron',
      cronInterval: 'Toutes les 2 minutes (*/2 * * * *)',
      cronCopied: 'Commande copiée!',
      messageTemplates: 'Modèles de messages',
      messageAr: 'Message en arabe',
      messageFr: 'Message en français',
      messageEn: 'Message en anglais',
      messageVars: 'Variables: {name} = nom du client, {link} = lien du magasin',
      storeUrl: 'URL du magasin',
      storeUrlDesc: 'URL complète du magasin (ex: https://agroyousfi.dz)',
      testWhatsApp: 'Envoyer un message test',
      testPhone: 'Numéro de test',
      testSending: 'Envoi en cours...',
      testSuccess: 'Message test envoyé avec succès!',
      testFailed: 'Échec de l\'envoi',
      apiSetupTitle: 'Guide de configuration',
      greenApiSetupSteps: [
        'Créez un compte gratuit sur green-api.com',
        'Créez une nouvelle instance',
        'Scannez le QR code avec votre téléphone',
        'Copiez l\'Instance ID et l\'API Token',
        'Collez les valeurs ici et enregistrez',
        'Configurez le cron pour l\'envoi automatique',
      ],
      businessApiSetupSteps: [
        'Allez sur Meta Business Suite (business.facebook.com)',
        'Créez une app WhatsApp Business',
        'Copiez le "Phone Number ID" et "Access Token" depuis les paramètres API',
        'Collez les valeurs ici et enregistrez',
        'Configurez le cron pour l\'envoi automatique',
      ],
      warningBan: 'Attention: L\'envoi massif peut entraîner le blocage de votre compte. Minimum 120 secondes recommandé.',
      fbMarketingTitle: 'Facebook Marketing API',
      fbMarketingDesc: 'Paramètres API pour créer automatiquement des publicités Facebook à partir des produits',
      fbAppId: 'App ID',
      fbAppSecret: 'App Secret',
      fbAccessTokenAds: 'Access Token',
      fbAccessTokenAdsDesc: 'Token d\'accès System User longue durée de Meta Business Manager',
      fbAdAccountId: 'Ad Account ID',
      fbAdAccountIdDesc: 'ID du compte sans le préfixe act_ (ex: 123456789)',
      fbPageId: 'Page ID',
      fbPageIdDesc: 'ID de la page Facebook pour les publicités',
      validateCredentials: 'Tester la connexion',
      validating: 'Test en cours...',
      validationSuccess: 'Connexion réussie!',
      validationFailed: 'Échec de la connexion',
      // Offers
      offersTitle: 'Offres',
      offersDesc: 'Proposez une remise ou la livraison gratuite pour inciter le client à finaliser',
      discountType: 'Type de remise',
      percentage: 'Pourcentage',
      fixedAmount: 'Montant fixe',
      discountValue: 'Valeur de la remise',
      freeShipping: 'Livraison gratuite',
      freeShippingDesc: 'Offrir la livraison gratuite avec le message de récupération',
      enableDiscount: 'Activer la remise',
      insertVariable: 'Insérer une variable',
      varAbandoned: 'N° panier',
      varDate: 'Date de création',
      varTime: 'Heure de création',
      varRecover: 'Lien de récupération',
      varName: 'Nom du client',
      varTotal: 'Montant total',
      varNewTotal: 'Montant après remise',
      varDiscount: 'Description remise',
      varItems: 'Nombre d\'articles',
      editorLang: 'Langue du message',
    },
    en: {
      settings: 'Settings',
      settingsDesc: 'Manage all store settings',
      storeSettings: 'Store Settings',
      storeDesc: 'Store name, email, phone, address',
      taxes: 'Taxes',
      taxesDesc: 'Tax rate and configuration',
      notifications: 'Notifications',
      notificationsDesc: 'Email and message notifications',
      users: 'Users',
      usersDesc: 'Manage user permissions',
      whatsapp: 'Marketing',
      marketingDesc: 'Facebook Pixel, WhatsApp, ads',
      save: 'Save Changes',
      saving: 'Saving...',
      saved: 'Settings saved',
      back: 'Back to Settings',
      fbPixelTitle: 'Facebook Pixel',
      fbPixelDesc: 'Connect Facebook Pixel to track events and optimize ads',
      fbPixelId: 'Pixel ID',
      fbPixelIdDesc: 'Facebook Pixel ID from Ads Manager (e.g., 123456789012345)',
      fbPixelIdPlaceholder: 'Enter Pixel ID',
      storeName: 'Store Name',
      storeEmail: 'Email',
      storePhone: 'Phone',
      storeAddress: 'Address',
      storeCurrency: 'Currency',
      storeLanguage: 'Default Language',
      storeDescription: 'Description',
      storeLogo: 'Store Logo',
      storeLogoDesc: 'Upload the store logo (displayed in header and invoices)',
      uploadLogo: 'Upload Logo',
      changeLogo: 'Change Logo',
      businessInfo: 'Business Information',
      businessInfoDesc: 'Commercial register and tax identification numbers (displayed on invoices)',
      storeRC: 'Commercial Register (RC)',
      storeNIF: 'Tax Identification Number (NIF)',
      storeNIS: 'Statistical Identification Number (NIS)',
      storeAI: 'Tax Article (AI)',
      socialMedia: 'Social Media',
      storeFacebook: 'Facebook Link',
      storeInstagram: 'Instagram Link',
      storeWebsite: 'Website',
      enableTax: 'Enable Tax',
      taxRate: 'Tax Rate (%)',
      taxIncluded: 'Tax included in price',
      emailOnNewOrder: 'Email on new order',
      emailOnLowStock: 'Email on low stock',
      smsOnNewOrder: 'SMS on new order',
      lowStockThreshold: 'Low stock threshold',
      userRole: 'User Role',
      admin: 'Admin',
      staff: 'Staff',
      adminDesc: 'Full access',
      staffDesc: 'Orders and products only',

      whatsappTitle: 'WhatsApp Settings',
      whatsappDesc: 'Connect WhatsApp to automatically send recovery messages for abandoned checkouts',
      enableWhatsApp: 'Enable WhatsApp',
      whatsappMode: 'Sending Mode',
      modeGreenApi: 'Personal WhatsApp (free)',
      modeGreenApiDesc: 'Use your personal WhatsApp number via Green API - free for limited use',
      modeBusinessApi: 'WhatsApp Business (paid)',
      modeBusinessApiDesc: 'Use Meta WhatsApp Business API - for high volume',
      greenApiInstanceId: 'Instance ID',
      greenApiInstanceIdDesc: 'Instance ID from your green-api.com account',
      greenApiToken: 'API Token',
      greenApiTokenDesc: 'Access token from your green-api.com account',
      rateLimitSeconds: 'Delay between messages (seconds)',
      rateLimitDesc: 'To protect your account from being banned - minimum 120 seconds recommended',
      phoneNumberId: 'Phone Number ID',
      phoneNumberIdDesc: 'From WhatsApp app settings in Meta Business',
      accessToken: 'Access Token',
      accessTokenDesc: 'Permanent access token from Meta Business',
      autoSendTitle: 'Auto Send',
      autoSendDesc: 'Automatically send WhatsApp messages to customers who didn\'t complete their orders',
      enableAutoSend: 'Enable Auto Send',
      delayMinutes: 'Delay before sending',
      delayMinutesDesc: 'Time to wait after the customer leaves the checkout page',
      maxRetries: 'Max retry attempts',
      maxRetriesDesc: 'Number of retries on send failure',
      maxPerRun: 'Max messages per run',
      maxPerRunDesc: 'Number of messages sent per cron execution',
      phoneCooldown: 'Phone cooldown period',
      phoneCooldownDesc: 'Only one message per phone number during this period',
      sendWindowStart: 'Send window start',
      sendWindowEnd: 'Send window end',
      sendWindowDesc: 'Messages are only sent during this time window (business hours)',
      cronSetupTitle: 'Automatic Setup (cPanel)',
      cronSetupDesc: 'Add this command to "Cron Jobs" in your cPanel control panel',
      cronCommand: 'Cron Command',
      cronInterval: 'Every 2 minutes (*/2 * * * *)',
      cronCopied: 'Command copied!',
      messageTemplates: 'Message Templates',
      messageAr: 'Arabic message',
      messageFr: 'French message',
      messageEn: 'English message',
      messageVars: 'Variables: {name} = customer name, {link} = store link',
      storeUrl: 'Store URL',
      storeUrlDesc: 'Full store URL (e.g., https://agroyousfi.dz)',
      testWhatsApp: 'Send test message',
      testPhone: 'Test phone number',
      testSending: 'Sending...',
      testSuccess: 'Test message sent successfully!',
      testFailed: 'Send failed',
      apiSetupTitle: 'Setup Guide',
      greenApiSetupSteps: [
        'Create a free account on green-api.com',
        'Create a new instance',
        'Scan the QR code with your phone to link WhatsApp',
        'Copy the Instance ID and API Token',
        'Paste the values here and save',
        'Set up the cron job for automatic sending',
      ],
      businessApiSetupSteps: [
        'Go to Meta Business Suite (business.facebook.com)',
        'Create a WhatsApp Business App',
        'Copy "Phone Number ID" and "Access Token" from API settings',
        'Paste the values here and save',
        'Set up the cron job for automatic sending',
      ],
      warningBan: 'Warning: Mass messaging may get your account banned. Minimum 120 seconds between messages recommended.',
      fbMarketingTitle: 'Facebook Marketing API',
      fbMarketingDesc: 'API settings for automatically creating Facebook ads from products',
      fbAppId: 'App ID',
      fbAppSecret: 'App Secret',
      fbAccessTokenAds: 'Access Token',
      fbAccessTokenAdsDesc: 'Long-lived System User token from Meta Business Manager',
      fbAdAccountId: 'Ad Account ID',
      fbAdAccountIdDesc: 'Account ID without the act_ prefix (e.g., 123456789)',
      fbPageId: 'Page ID',
      fbPageIdDesc: 'Facebook Page ID for publishing ads',
      validateCredentials: 'Test Connection',
      validating: 'Testing...',
      validationSuccess: 'Connection successful!',
      validationFailed: 'Connection failed',
      // Offers
      offersTitle: 'Offers',
      offersDesc: 'Offer a discount or free shipping to encourage order completion',
      discountType: 'Discount type',
      percentage: 'Percentage',
      fixedAmount: 'Fixed amount',
      discountValue: 'Discount value',
      freeShipping: 'Free shipping',
      freeShippingDesc: 'Offer free shipping with the recovery message',
      enableDiscount: 'Enable discount',
      insertVariable: 'Insert variable',
      varAbandoned: 'Checkout #',
      varDate: 'Created date',
      varTime: 'Created time',
      varRecover: 'Recover URL',
      varName: 'Customer name',
      varTotal: 'Total amount',
      varNewTotal: 'Total after discount',
      varDiscount: 'Discount description',
      varItems: 'Items count',
      editorLang: 'Message language',
    }
  };

  const text = l[language] || l.ar;

  // Load settings from backend based on active section
  useEffect(() => {
    if (activeSection === 'marketing') {
      fetchWhatsAppSettings();
    }
    if (activeSection === 'store') {
      fetchStoreSettings();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeSection]);

  const fetchStoreSettings = async () => {
    try {
      setLoadingStore(true);
      const res = await axios.get(`${API}/admin/settings/store`, { withCredentials: true });
      const data = res.data;
      setStoreSettings(prev => ({
        ...prev,
        store_name: data.store_name || '',
        store_email: data.store_email || '',
        store_phone: data.store_phone || '',
        store_address: data.store_address || '',
        store_currency: data.store_currency || 'DZD',
        store_language: data.store_language || 'ar',
        store_description: data.store_description || '',
        store_logo: data.store_logo || '',
        store_rc: data.store_rc || '',
        store_nif: data.store_nif || '',
        store_nis: data.store_nis || '',
        store_ai: data.store_ai || '',
        store_facebook: data.store_facebook || '',
        store_instagram: data.store_instagram || '',
        store_website: data.store_website || '',
      }));
    } catch (error) {
      // Settings may not exist yet
    } finally {
      setLoadingStore(false);
    }
  };

  const handleLogoUpload = async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    try {
      setUploadingLogo(true);
      const formData = new FormData();
      formData.append('file', file);
      const res = await axios.post(`${API}/upload`, formData, {
        withCredentials: true,
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      setStoreSettings(prev => ({ ...prev, store_logo: res.data.url }));
      toast.success(language === 'ar' ? 'تم رفع الشعار' : 'Logo uploaded');
    } catch (error) {
      toast.error(language === 'ar' ? 'خطأ في رفع الشعار' : 'Error uploading logo');
    } finally {
      setUploadingLogo(false);
    }
  };

  const fetchWhatsAppSettings = async () => {
    try {
      setLoadingWhatsApp(true);
      const res = await axios.get(`${API}/admin/settings/whatsapp`, { withCredentials: true });
      const data = res.data;
      setWhatsappSettings({
        whatsapp_enabled: data.whatsapp_enabled === 'true',
        whatsapp_mode: data.whatsapp_mode || 'green_api',
        whatsapp_phone_number_id: data.whatsapp_phone_number_id || '',
        whatsapp_access_token: data.whatsapp_access_token || '',
        green_api_instance_id: data.green_api_instance_id || '',
        green_api_token: data.green_api_token || '',
        whatsapp_rate_limit_seconds: data.whatsapp_rate_limit_seconds || '120',
        whatsapp_auto_send: data.whatsapp_auto_send === 'true',
        whatsapp_delay_minutes: data.whatsapp_delay_minutes || '30',
        whatsapp_max_retries: data.whatsapp_max_retries || '5',
        whatsapp_max_per_run: data.whatsapp_max_per_run || '10',
        whatsapp_phone_cooldown_minutes: data.whatsapp_phone_cooldown_minutes || '1440',
        whatsapp_send_window_start: data.whatsapp_send_window_start || '9',
        whatsapp_send_window_end: data.whatsapp_send_window_end || '21',
        whatsapp_message_ar: data.whatsapp_message_ar || '',
        whatsapp_message_fr: data.whatsapp_message_fr || '',
        whatsapp_message_en: data.whatsapp_message_en || '',
        store_url: data.store_url || '',
        fb_pixel_id: data.fb_pixel_id || '',
        fb_app_id: data.fb_app_id || '',
        fb_app_secret: data.fb_app_secret || '',
        fb_access_token: data.fb_access_token || '',
        fb_ad_account_id: data.fb_ad_account_id || '',
        fb_page_id: data.fb_page_id || '',
        // Offers
        offer_discount_enabled: data.offer_discount_enabled === 'true',
        offer_discount_type: data.offer_discount_type || 'percentage',
        offer_discount_value: data.offer_discount_value || '10',
        offer_free_shipping: data.offer_free_shipping === 'true',
      });
    } catch (error) {
      // Settings may not exist yet, use defaults
    } finally {
      setLoadingWhatsApp(false);
    }
  };

  const handleSave = async () => {
    try {
      setSaving(true);

      if (activeSection === 'marketing') {
        await axios.put(`${API}/admin/settings`, {
          whatsapp_enabled: whatsappSettings.whatsapp_enabled ? 'true' : 'false',
          whatsapp_mode: whatsappSettings.whatsapp_mode,
          whatsapp_phone_number_id: whatsappSettings.whatsapp_phone_number_id,
          whatsapp_access_token: whatsappSettings.whatsapp_access_token,
          green_api_instance_id: whatsappSettings.green_api_instance_id,
          green_api_token: whatsappSettings.green_api_token,
          whatsapp_rate_limit_seconds: whatsappSettings.whatsapp_rate_limit_seconds,
          whatsapp_auto_send: whatsappSettings.whatsapp_auto_send ? 'true' : 'false',
          whatsapp_delay_minutes: whatsappSettings.whatsapp_delay_minutes,
          whatsapp_max_retries: whatsappSettings.whatsapp_max_retries,
          whatsapp_max_per_run: whatsappSettings.whatsapp_max_per_run,
          whatsapp_phone_cooldown_minutes: whatsappSettings.whatsapp_phone_cooldown_minutes,
          whatsapp_send_window_start: whatsappSettings.whatsapp_send_window_start,
          whatsapp_send_window_end: whatsappSettings.whatsapp_send_window_end,
          whatsapp_message_ar: whatsappSettings.whatsapp_message_ar,
          whatsapp_message_fr: whatsappSettings.whatsapp_message_fr,
          whatsapp_message_en: whatsappSettings.whatsapp_message_en,
          store_url: whatsappSettings.store_url,
          fb_pixel_id: whatsappSettings.fb_pixel_id,
          fb_app_id: whatsappSettings.fb_app_id,
          fb_app_secret: whatsappSettings.fb_app_secret,
          fb_access_token: whatsappSettings.fb_access_token,
          fb_ad_account_id: whatsappSettings.fb_ad_account_id,
          fb_page_id: whatsappSettings.fb_page_id,
          // Offers
          offer_discount_enabled: whatsappSettings.offer_discount_enabled ? 'true' : 'false',
          offer_discount_type: whatsappSettings.offer_discount_type,
          offer_discount_value: whatsappSettings.offer_discount_value,
          offer_free_shipping: whatsappSettings.offer_free_shipping ? 'true' : 'false',
        }, { withCredentials: true });
      } else if (activeSection === 'store') {
        await axios.put(`${API}/admin/settings`, storeSettings, { withCredentials: true });
        refreshSettings();
      } else {
        // For other sections - placeholder for now
        await new Promise(resolve => setTimeout(resolve, 500));
      }

      toast.success(text.saved);
    } catch (error) {
      toast.error(error.response?.data?.detail || 'Error saving settings');
    } finally {
      setSaving(false);
    }
  };

  const handleTestWhatsApp = async () => {
    if (!testPhone) return;
    try {
      setTestingWhatsApp(true);
      const message = whatsappSettings.whatsapp_message_ar
        .replace('{name}', 'Test')
        .replace('{link}', whatsappSettings.store_url || 'https://example.com')
        .replace('{total}', '5,200 د.ج')
        .replace('{new_total}', '4,680 د.ج')
        .replace('{discount}', '10%')
        .replace('{items_count}', '3')
        .replace('{checkout_id}', '#TEST123');

      await axios.post(`${API}/admin/settings/whatsapp/test`, {
        phone: testPhone,
        message,
      }, { withCredentials: true });

      toast.success(text.testSuccess);
    } catch (error) {
      toast.error(text.testFailed + ': ' + (error.response?.data?.detail || 'Error'));
    } finally {
      setTestingWhatsApp(false);
    }
  };

  const handleValidateFbCredentials = async () => {
    try {
      setValidatingFb(true);
      await axios.post(`${API}/admin/facebook-ads/validate`, {}, { withCredentials: true });
      toast.success(text.validationSuccess);
    } catch (error) {
      toast.error(text.validationFailed + ': ' + (error.response?.data?.detail || 'Error'));
    } finally {
      setValidatingFb(false);
    }
  };

  const BackArrow = isRTL ? ArrowRight : ArrowLeft;

  // Settings Dashboard categories
  const settingsCategories = [
    {
      id: 'store',
      icon: Store,
      title: text.storeSettings,
      description: text.storeDesc,
      color: 'text-primary',
      bgColor: 'bg-primary/10',
      borderColor: 'hover:border-primary/30',
    },
    {
      id: 'marketing',
      icon: Megaphone,
      title: text.whatsapp,
      description: text.marketingDesc,
      color: 'text-green-600',
      bgColor: 'bg-green-100 dark:bg-green-900/30',
      borderColor: 'hover:border-green-300',
    },
    {
      id: 'taxes',
      icon: CreditCard,
      title: text.taxes,
      description: text.taxesDesc,
      color: 'text-amber-600',
      bgColor: 'bg-amber-100 dark:bg-amber-900/30',
      borderColor: 'hover:border-amber-300',
    },
    {
      id: 'notifications',
      icon: Bell,
      title: text.notifications,
      description: text.notificationsDesc,
      color: 'text-blue-600',
      bgColor: 'bg-blue-100 dark:bg-blue-900/30',
      borderColor: 'hover:border-blue-300',
    },
    {
      id: 'users',
      icon: Users,
      title: text.users,
      description: text.usersDesc,
      color: 'text-purple-600',
      bgColor: 'bg-purple-100 dark:bg-purple-900/30',
      borderColor: 'hover:border-purple-300',
    },
  ];

  // ─── Render: Settings Dashboard ───
  const renderDashboard = () => (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{text.settings}</h1>
        <p className="text-muted-foreground mt-1">{text.settingsDesc}</p>
      </div>
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {settingsCategories.map((cat) => (
          <Link
            key={cat.id}
            to={`/admin/settings/${cat.id}`}
            className={`group block p-5 border rounded-xl transition-all hover:shadow-md ${cat.borderColor}`}
          >
            <div className="flex items-start gap-4">
              <div className={`h-12 w-12 rounded-xl ${cat.bgColor} flex items-center justify-center shrink-0`}>
                <cat.icon className={`h-6 w-6 ${cat.color}`} />
              </div>
              <div className="flex-1 min-w-0">
                <h3 className="font-semibold text-base">{cat.title}</h3>
                <p className="text-sm text-muted-foreground mt-1">{cat.description}</p>
              </div>
            </div>
          </Link>
        ))}
      </div>
    </div>
  );

  // ─── Render: Section Header with Save Button ───
  const renderSectionHeader = (title) => (
    <div className="flex items-center justify-between">
      <div className="flex items-center gap-3">
        <Link
          to="/admin/settings"
          className="p-2 rounded-lg hover:bg-muted transition-colors"
        >
          <BackArrow className="h-5 w-5" />
        </Link>
        <h1 className="text-2xl font-bold">{title}</h1>
      </div>
      <Button onClick={handleSave} disabled={saving}>
        {saving ? (
          <>
            <Loader2 className="h-4 w-4 me-2 animate-spin" />
            {text.saving}
          </>
        ) : (
          <>
            <Save className="h-4 w-4 me-2" />
            {text.save}
          </>
        )}
      </Button>
    </div>
  );

  // ─── Render: Store Settings Section ───
  const renderStoreSection = () => (
    <div className="space-y-6">
      {renderSectionHeader(text.storeSettings)}

      {loadingStore ? (
        <div className="flex items-center justify-center py-12">
          <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
        </div>
      ) : (
        <>
          {/* Logo Upload */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Image className="h-5 w-5 text-primary" />
                {text.storeLogo}
              </CardTitle>
              <CardDescription>{text.storeLogoDesc}</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-6">
                {storeSettings.store_logo ? (
                  <img
                    src={storeSettings.store_logo}
                    alt="Store Logo"
                    className="h-24 w-24 rounded-xl object-cover border-2 border-border shadow-sm"
                  />
                ) : (
                  <div className="h-24 w-24 rounded-xl border-2 border-dashed border-border flex items-center justify-center bg-muted">
                    <Image className="h-8 w-8 text-muted-foreground" />
                  </div>
                )}
                <div className="space-y-2">
                  <label htmlFor="logo-upload">
                    <Button
                      variant="outline"
                      disabled={uploadingLogo}
                      onClick={() => document.getElementById('logo-upload').click()}
                    >
                      {uploadingLogo ? (
                        <><Loader2 className="h-4 w-4 me-2 animate-spin" />{text.saving}</>
                      ) : (
                        <><Upload className="h-4 w-4 me-2" />{storeSettings.store_logo ? text.changeLogo : text.uploadLogo}</>
                      )}
                    </Button>
                  </label>
                  <input
                    id="logo-upload"
                    type="file"
                    accept="image/*"
                    className="hidden"
                    onChange={handleLogoUpload}
                  />
                  {storeSettings.store_logo && (
                    <Button
                      variant="ghost"
                      size="sm"
                      className="text-destructive"
                      onClick={() => setStoreSettings(prev => ({ ...prev, store_logo: '' }))}
                    >
                      {language === 'ar' ? 'إزالة' : language === 'fr' ? 'Supprimer' : 'Remove'}
                    </Button>
                  )}
                </div>
              </div>
            </CardContent>
          </Card>

          {/* General Store Info */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Store className="h-5 w-5 text-primary" />
                {text.storeSettings}
              </CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>{text.storeName}</Label>
                  <Input
                    value={storeSettings.store_name}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_name: e.target.value }))}
                    placeholder="AgroYousfi"
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storeEmail}</Label>
                  <Input
                    type="email"
                    value={storeSettings.store_email}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_email: e.target.value }))}
                    placeholder="contact@example.dz"
                    dir="ltr"
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storePhone}</Label>
                  <Input
                    value={storeSettings.store_phone}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_phone: e.target.value }))}
                    placeholder="+213 555 123 456"
                    dir="ltr"
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storeAddress}</Label>
                  <Input
                    value={storeSettings.store_address}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_address: e.target.value }))}
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storeCurrency}</Label>
                  <Select value={storeSettings.store_currency} onValueChange={(v) => setStoreSettings(prev => ({ ...prev, store_currency: v }))}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="DZD">د.ج (DZD)</SelectItem>
                      <SelectItem value="EUR">€ (EUR)</SelectItem>
                      <SelectItem value="USD">$ (USD)</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div className="space-y-2">
                  <Label>{text.storeLanguage}</Label>
                  <Select value={storeSettings.store_language} onValueChange={(v) => setStoreSettings(prev => ({ ...prev, store_language: v }))}>
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="ar">العربية</SelectItem>
                      <SelectItem value="fr">Français</SelectItem>
                      <SelectItem value="en">English</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              <div className="space-y-2">
                <Label>{text.storeDescription}</Label>
                <Textarea
                  value={storeSettings.store_description}
                  onChange={(e) => setStoreSettings(prev => ({ ...prev, store_description: e.target.value }))}
                  rows={3}
                />
              </div>
            </CardContent>
          </Card>

          {/* Business Information (RC, NIF, NIS, AI) */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Building2 className="h-5 w-5 text-amber-600" />
                {text.businessInfo}
              </CardTitle>
              <CardDescription>{text.businessInfoDesc}</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>{text.storeRC}</Label>
                  <Input
                    value={storeSettings.store_rc}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_rc: e.target.value }))}
                    placeholder="00/00-0000000B00"
                    dir="ltr"
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storeNIF}</Label>
                  <Input
                    value={storeSettings.store_nif}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_nif: e.target.value }))}
                    placeholder="000000000000000"
                    dir="ltr"
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storeNIS}</Label>
                  <Input
                    value={storeSettings.store_nis}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_nis: e.target.value }))}
                    placeholder="000000000000000"
                    dir="ltr"
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storeAI}</Label>
                  <Input
                    value={storeSettings.store_ai}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_ai: e.target.value }))}
                    placeholder="00000000000"
                    dir="ltr"
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Social Media */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Globe className="h-5 w-5 text-blue-600" />
                {text.socialMedia}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>{text.storeFacebook}</Label>
                  <Input
                    value={storeSettings.store_facebook}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_facebook: e.target.value }))}
                    placeholder="https://facebook.com/..."
                    dir="ltr"
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.storeInstagram}</Label>
                  <Input
                    value={storeSettings.store_instagram}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_instagram: e.target.value }))}
                    placeholder="https://instagram.com/..."
                    dir="ltr"
                  />
                </div>
                <div className="space-y-2 md:col-span-2">
                  <Label>{text.storeWebsite}</Label>
                  <Input
                    value={storeSettings.store_website}
                    onChange={(e) => setStoreSettings(prev => ({ ...prev, store_website: e.target.value }))}
                    placeholder="https://www.example.dz"
                    dir="ltr"
                  />
                </div>
              </div>
            </CardContent>
          </Card>
        </>
      )}
    </div>
  );

  // ─── Render: Marketing Section ───
  const renderMarketingSection = () => (
    <div className="space-y-6">
      {renderSectionHeader(text.whatsapp)}
      {loadingWhatsApp ? (
        <div className="flex items-center justify-center py-12">
          <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
        </div>
      ) : (
        <>
          {/* Facebook Pixel */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <BarChart className="h-5 w-5 text-blue-600" />
                {text.fbPixelTitle}
              </CardTitle>
              <CardDescription>{text.fbPixelDesc}</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                <Label>{text.fbPixelId}</Label>
                <Input
                  value={whatsappSettings.fb_pixel_id}
                  onChange={(e) => setWhatsappSettings(prev => ({ ...prev, fb_pixel_id: e.target.value }))}
                  placeholder={text.fbPixelIdPlaceholder}
                  dir="ltr"
                />
                <p className="text-xs text-muted-foreground">{text.fbPixelIdDesc}</p>
              </div>
            </CardContent>
          </Card>

          {/* Facebook Marketing API */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Megaphone className="h-5 w-5 text-blue-600" />
                {text.fbMarketingTitle}
              </CardTitle>
              <CardDescription>{text.fbMarketingDesc}</CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>{text.fbAppId}</Label>
                  <Input
                    value={whatsappSettings.fb_app_id}
                    onChange={(e) => setWhatsappSettings(prev => ({ ...prev, fb_app_id: e.target.value }))}
                    placeholder="123456789"
                    dir="ltr"
                  />
                </div>
                <div className="space-y-2">
                  <Label>{text.fbAppSecret}</Label>
                  <Input
                    type="password"
                    value={whatsappSettings.fb_app_secret}
                    onChange={(e) => setWhatsappSettings(prev => ({ ...prev, fb_app_secret: e.target.value }))}
                    dir="ltr"
                  />
                </div>
              </div>
              <div className="space-y-2">
                <Label>{text.fbAccessTokenAds}</Label>
                <Input
                  type="password"
                  value={whatsappSettings.fb_access_token}
                  onChange={(e) => setWhatsappSettings(prev => ({ ...prev, fb_access_token: e.target.value }))}
                  placeholder="EAAxxxxxxxx..."
                  dir="ltr"
                />
                <p className="text-xs text-muted-foreground">{text.fbAccessTokenAdsDesc}</p>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>{text.fbAdAccountId}</Label>
                  <Input
                    value={whatsappSettings.fb_ad_account_id}
                    onChange={(e) => setWhatsappSettings(prev => ({ ...prev, fb_ad_account_id: e.target.value }))}
                    placeholder="123456789"
                    dir="ltr"
                  />
                  <p className="text-xs text-muted-foreground">{text.fbAdAccountIdDesc}</p>
                </div>
                <div className="space-y-2">
                  <Label>{text.fbPageId}</Label>
                  <Input
                    value={whatsappSettings.fb_page_id}
                    onChange={(e) => setWhatsappSettings(prev => ({ ...prev, fb_page_id: e.target.value }))}
                    placeholder="987654321"
                    dir="ltr"
                  />
                  <p className="text-xs text-muted-foreground">{text.fbPageIdDesc}</p>
                </div>
              </div>
              <Button
                variant="outline"
                onClick={handleValidateFbCredentials}
                disabled={validatingFb || !whatsappSettings.fb_access_token || !whatsappSettings.fb_ad_account_id}
              >
                {validatingFb ? (
                  <><Loader2 className="h-4 w-4 me-2 animate-spin" />{text.validating}</>
                ) : (
                  text.validateCredentials
                )}
              </Button>
            </CardContent>
          </Card>

          {/* WhatsApp Connection */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <MessageCircle className="h-5 w-5 text-green-600" />
                {text.whatsappTitle}
              </CardTitle>
              <CardDescription>{text.whatsappDesc}</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              {/* Enable toggle */}
              <div className="flex items-center justify-between p-4 bg-muted/50 rounded-xl">
                <div className="flex items-center gap-3">
                  <div className={`h-3 w-3 rounded-full ${whatsappSettings.whatsapp_enabled ? 'bg-green-500' : 'bg-gray-300'}`} />
                  <Label className="text-base font-medium">{text.enableWhatsApp}</Label>
                </div>
                <Switch
                  checked={whatsappSettings.whatsapp_enabled}
                  onCheckedChange={(checked) => setWhatsappSettings(prev => ({ ...prev, whatsapp_enabled: checked }))}
                />
              </div>

              {whatsappSettings.whatsapp_enabled && (
                <>
                  {/* Mode Selection */}
                  <div className="space-y-3">
                    <Label className="text-sm font-medium">{text.whatsappMode}</Label>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <button
                        type="button"
                        onClick={() => setWhatsappSettings(prev => ({ ...prev, whatsapp_mode: 'green_api' }))}
                        className={`p-4 border-2 rounded-xl text-start transition-all ${
                          whatsappSettings.whatsapp_mode === 'green_api'
                            ? 'border-green-500 bg-green-50 dark:bg-green-950/20'
                            : 'border-muted hover:border-muted-foreground/30'
                        }`}
                      >
                        <div className="flex items-center gap-2 mb-1">
                          <Smartphone className="h-4 w-4 text-green-600" />
                          <span className="font-medium text-sm">{text.modeGreenApi}</span>
                        </div>
                        <p className="text-xs text-muted-foreground">{text.modeGreenApiDesc}</p>
                      </button>
                      <button
                        type="button"
                        onClick={() => setWhatsappSettings(prev => ({ ...prev, whatsapp_mode: 'business_api' }))}
                        className={`p-4 border-2 rounded-xl text-start transition-all ${
                          whatsappSettings.whatsapp_mode === 'business_api'
                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/20'
                            : 'border-muted hover:border-muted-foreground/30'
                        }`}
                      >
                        <div className="flex items-center gap-2 mb-1">
                          <Globe className="h-4 w-4 text-blue-600" />
                          <span className="font-medium text-sm">{text.modeBusinessApi}</span>
                        </div>
                        <p className="text-xs text-muted-foreground">{text.modeBusinessApiDesc}</p>
                      </button>
                    </div>
                  </div>

                  {/* Green API Credentials */}
                  {whatsappSettings.whatsapp_mode === 'green_api' && (
                    <div className="space-y-4">
                      <div className="space-y-2">
                        <Label>{text.greenApiInstanceId}</Label>
                        <Input
                          value={whatsappSettings.green_api_instance_id}
                          onChange={(e) => setWhatsappSettings(prev => ({ ...prev, green_api_instance_id: e.target.value }))}
                          placeholder="1101234567"
                          dir="ltr"
                        />
                        <p className="text-xs text-muted-foreground">{text.greenApiInstanceIdDesc}</p>
                      </div>
                      <div className="space-y-2">
                        <Label>{text.greenApiToken}</Label>
                        <Input
                          type="password"
                          value={whatsappSettings.green_api_token}
                          onChange={(e) => setWhatsappSettings(prev => ({ ...prev, green_api_token: e.target.value }))}
                          placeholder="abc123def456..."
                          dir="ltr"
                        />
                        <p className="text-xs text-muted-foreground">{text.greenApiTokenDesc}</p>
                      </div>
                      <div className="space-y-2">
                        <Label>{text.rateLimitSeconds}</Label>
                        <Select
                          value={whatsappSettings.whatsapp_rate_limit_seconds}
                          onValueChange={(v) => setWhatsappSettings(prev => ({ ...prev, whatsapp_rate_limit_seconds: v }))}
                        >
                          <SelectTrigger>
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="30">30 {language === 'ar' ? 'ثانية' : 's'}</SelectItem>
                            <SelectItem value="60">60 {language === 'ar' ? 'ثانية' : 's'}</SelectItem>
                            <SelectItem value="120">120 {language === 'ar' ? 'ثانية' : 's'} ({language === 'ar' ? 'موصى به' : language === 'fr' ? 'recommandé' : 'recommended'})</SelectItem>
                            <SelectItem value="180">180 {language === 'ar' ? 'ثانية' : 's'}</SelectItem>
                            <SelectItem value="300">300 {language === 'ar' ? 'ثانية' : 's'} (5 min)</SelectItem>
                          </SelectContent>
                        </Select>
                        <p className="text-xs text-muted-foreground">{text.rateLimitDesc}</p>
                      </div>
                      {/* Ban warning */}
                      <div className="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                        <p className="text-xs text-amber-700 dark:text-amber-400 flex items-start gap-2">
                          <AlertCircle className="h-4 w-4 shrink-0 mt-0.5" />
                          {text.warningBan}
                        </p>
                      </div>
                    </div>
                  )}

                  {/* Business API Credentials */}
                  {whatsappSettings.whatsapp_mode === 'business_api' && (
                    <div className="space-y-4">
                      <div className="space-y-2">
                        <Label>{text.phoneNumberId}</Label>
                        <Input
                          value={whatsappSettings.whatsapp_phone_number_id}
                          onChange={(e) => setWhatsappSettings(prev => ({ ...prev, whatsapp_phone_number_id: e.target.value }))}
                          placeholder="123456789012345"
                          dir="ltr"
                        />
                        <p className="text-xs text-muted-foreground">{text.phoneNumberIdDesc}</p>
                      </div>
                      <div className="space-y-2">
                        <Label>{text.accessToken}</Label>
                        <Input
                          type="password"
                          value={whatsappSettings.whatsapp_access_token}
                          onChange={(e) => setWhatsappSettings(prev => ({ ...prev, whatsapp_access_token: e.target.value }))}
                          placeholder="EAAxxxxxxxx..."
                          dir="ltr"
                        />
                        <p className="text-xs text-muted-foreground">{text.accessTokenDesc}</p>
                      </div>
                    </div>
                  )}

                  {/* Store URL */}
                  <div className="space-y-2">
                    <Label>{text.storeUrl}</Label>
                    <Input
                      value={whatsappSettings.store_url}
                      onChange={(e) => setWhatsappSettings(prev => ({ ...prev, store_url: e.target.value }))}
                      placeholder="https://agroyousfi.dz"
                      dir="ltr"
                    />
                    <p className="text-xs text-muted-foreground">{text.storeUrlDesc}</p>
                  </div>

                  {/* Test Message */}
                  <div className="p-4 border rounded-xl space-y-3">
                    <Label className="text-sm font-medium">{text.testWhatsApp}</Label>
                    <div className="flex gap-2">
                      <Input
                        value={testPhone}
                        onChange={(e) => setTestPhone(e.target.value)}
                        placeholder="0555 123 456"
                        dir="ltr"
                        className="flex-1"
                      />
                      <Button
                        onClick={handleTestWhatsApp}
                        disabled={testingWhatsApp || !testPhone}
                        variant="outline"
                      >
                        {testingWhatsApp ? (
                          <Loader2 className="h-4 w-4 animate-spin" />
                        ) : (
                          <Send className="h-4 w-4" />
                        )}
                      </Button>
                    </div>
                  </div>
                </>
              )}
            </CardContent>
          </Card>

          {/* Auto Send Settings */}
          {whatsappSettings.whatsapp_enabled && (
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Clock className="h-5 w-5 text-blue-600" />
                  {text.autoSendTitle}
                </CardTitle>
                <CardDescription>{text.autoSendDesc}</CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                {/* Auto-send toggle */}
                <div className="flex items-center justify-between p-4 bg-muted/50 rounded-xl">
                  <div className="flex items-center gap-3">
                    <div className={`h-3 w-3 rounded-full ${whatsappSettings.whatsapp_auto_send ? 'bg-green-500' : 'bg-gray-300'}`} />
                    <Label className="text-base font-medium">{text.enableAutoSend}</Label>
                  </div>
                  <Switch
                    checked={whatsappSettings.whatsapp_auto_send}
                    onCheckedChange={(checked) => setWhatsappSettings(prev => ({ ...prev, whatsapp_auto_send: checked }))}
                  />
                </div>

                {whatsappSettings.whatsapp_auto_send && (
                  <>
                    {/* Settings Grid — 2 columns */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      {/* Delay */}
                      <div className="space-y-1.5">
                        <Label className="text-sm">{text.delayMinutes}</Label>
                        <Select
                          value={whatsappSettings.whatsapp_delay_minutes}
                          onValueChange={(v) => setWhatsappSettings(prev => ({ ...prev, whatsapp_delay_minutes: v }))}
                        >
                          <SelectTrigger className="h-9">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="2">2 {language === 'ar' ? 'دقيقة' : 'min'}</SelectItem>
                            <SelectItem value="5">5 {language === 'ar' ? 'دقائق' : 'min'}</SelectItem>
                            <SelectItem value="10">10 {language === 'ar' ? 'دقائق' : 'min'}</SelectItem>
                            <SelectItem value="15">15 {language === 'ar' ? 'دقيقة' : 'min'}</SelectItem>
                            <SelectItem value="30">30 {language === 'ar' ? 'دقيقة' : 'min'}</SelectItem>
                            <SelectItem value="60">1 {language === 'ar' ? 'ساعة' : 'hour'}</SelectItem>
                            <SelectItem value="120">2 {language === 'ar' ? 'ساعة' : 'hours'}</SelectItem>
                            <SelectItem value="360">6 {language === 'ar' ? 'ساعات' : 'hours'}</SelectItem>
                            <SelectItem value="1440">24 {language === 'ar' ? 'ساعة' : 'hours'}</SelectItem>
                          </SelectContent>
                        </Select>
                        <p className="text-[11px] text-muted-foreground">{text.delayMinutesDesc}</p>
                      </div>

                      {/* Max retries */}
                      <div className="space-y-1.5">
                        <Label className="text-sm">{text.maxRetries}</Label>
                        <Select
                          value={whatsappSettings.whatsapp_max_retries}
                          onValueChange={(v) => setWhatsappSettings(prev => ({ ...prev, whatsapp_max_retries: v }))}
                        >
                          <SelectTrigger className="h-9">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="1">1</SelectItem>
                            <SelectItem value="3">3</SelectItem>
                            <SelectItem value="5">5 ({language === 'ar' ? 'موصى به' : language === 'fr' ? 'recommandé' : 'recommended'})</SelectItem>
                            <SelectItem value="10">10</SelectItem>
                          </SelectContent>
                        </Select>
                        <p className="text-[11px] text-muted-foreground">{text.maxRetriesDesc}</p>
                      </div>

                      {/* Max per run */}
                      <div className="space-y-1.5">
                        <Label className="text-sm">{text.maxPerRun}</Label>
                        <Select
                          value={whatsappSettings.whatsapp_max_per_run}
                          onValueChange={(v) => setWhatsappSettings(prev => ({ ...prev, whatsapp_max_per_run: v }))}
                        >
                          <SelectTrigger className="h-9">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="1">1</SelectItem>
                            <SelectItem value="3">3</SelectItem>
                            <SelectItem value="5">5</SelectItem>
                            <SelectItem value="10">10 ({language === 'ar' ? 'موصى به' : language === 'fr' ? 'recommandé' : 'recommended'})</SelectItem>
                            <SelectItem value="20">20</SelectItem>
                          </SelectContent>
                        </Select>
                        <p className="text-[11px] text-muted-foreground">{text.maxPerRunDesc}</p>
                      </div>

                      {/* Phone cooldown */}
                      <div className="space-y-1.5">
                        <Label className="text-sm">{text.phoneCooldown}</Label>
                        <Select
                          value={whatsappSettings.whatsapp_phone_cooldown_minutes}
                          onValueChange={(v) => setWhatsappSettings(prev => ({ ...prev, whatsapp_phone_cooldown_minutes: v }))}
                        >
                          <SelectTrigger className="h-9">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="60">1 {language === 'ar' ? 'ساعة' : 'hour'}</SelectItem>
                            <SelectItem value="360">6 {language === 'ar' ? 'ساعات' : 'hours'}</SelectItem>
                            <SelectItem value="720">12 {language === 'ar' ? 'ساعة' : 'hours'}</SelectItem>
                            <SelectItem value="1440">24 {language === 'ar' ? 'ساعة' : 'hours'} ({language === 'ar' ? 'موصى به' : language === 'fr' ? 'recommandé' : 'recommended'})</SelectItem>
                            <SelectItem value="4320">3 {language === 'ar' ? 'أيام' : 'days'}</SelectItem>
                            <SelectItem value="10080">7 {language === 'ar' ? 'أيام' : 'days'}</SelectItem>
                          </SelectContent>
                        </Select>
                        <p className="text-[11px] text-muted-foreground">{text.phoneCooldownDesc}</p>
                      </div>
                    </div>

                    {/* Send window */}
                    <div className="space-y-1.5">
                      <Label className="text-sm">{text.sendWindowDesc}</Label>
                      <div className="grid grid-cols-2 gap-4">
                        <div className="space-y-1">
                          <Label className="text-xs text-muted-foreground">{text.sendWindowStart}</Label>
                          <Select
                            value={whatsappSettings.whatsapp_send_window_start}
                            onValueChange={(v) => setWhatsappSettings(prev => ({ ...prev, whatsapp_send_window_start: v }))}
                          >
                            <SelectTrigger className="h-9">
                              <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                              {[...Array(24)].map((_, i) => (
                                <SelectItem key={i} value={String(i)}>{String(i).padStart(2, '0')}:00</SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </div>
                        <div className="space-y-1">
                          <Label className="text-xs text-muted-foreground">{text.sendWindowEnd}</Label>
                          <Select
                            value={whatsappSettings.whatsapp_send_window_end}
                            onValueChange={(v) => setWhatsappSettings(prev => ({ ...prev, whatsapp_send_window_end: v }))}
                          >
                            <SelectTrigger className="h-9">
                              <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                              {[...Array(24)].map((_, i) => (
                                <SelectItem key={i} value={String(i)}>{String(i).padStart(2, '0')}:00</SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </div>
                      </div>
                    </div>

                    {/* Cron Setup Guide */}
                    <div className="p-4 bg-muted/50 rounded-xl space-y-3">
                      <div className="flex items-center gap-2">
                        <Clock className="h-4 w-4 text-blue-600" />
                        <Label className="text-sm font-medium">{text.cronSetupTitle}</Label>
                      </div>
                      <p className="text-xs text-muted-foreground">{text.cronSetupDesc}</p>
                      <div className="space-y-2">
                        <Label className="text-xs">{text.cronInterval}</Label>
                        <div className="flex gap-2">
                          <code className="flex-1 p-2 bg-background border rounded-lg text-xs font-mono break-all" dir="ltr">
                            */2 * * * * /usr/bin/php82 {whatsappSettings.store_url ? new URL(whatsappSettings.store_url).pathname || '/home/USER/public_html' : '/home/USER/public_html'}/api/cron/abandoned_whatsapp.php &gt;&gt; /dev/null 2&gt;&amp;1
                          </code>
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => {
                              const path = whatsappSettings.store_url ? '/home/USER/public_html' : '/home/USER/public_html';
                              navigator.clipboard.writeText(`*/2 * * * * /usr/bin/php82 ${path}/api/cron/abandoned_whatsapp.php >> /dev/null 2>&1`);
                              toast.success(text.cronCopied);
                            }}
                          >
                            {language === 'ar' ? 'نسخ' : 'Copy'}
                          </Button>
                        </div>
                      </div>
                    </div>

                  </>
                )}
              </CardContent>
            </Card>
          )}

          {/* Offers — WEBI style */}
          {whatsappSettings.whatsapp_enabled && (
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Gift className="h-5 w-5 text-pink-600" />
                  {text.offersTitle}
                </CardTitle>
                <CardDescription>{text.offersDesc}</CardDescription>
              </CardHeader>
              <CardContent className="space-y-5">
                {/* Discount toggle */}
                <div className="flex items-center justify-between p-4 bg-muted/50 rounded-xl">
                  <div className="flex items-center gap-3">
                    <Percent className="h-4 w-4 text-pink-500" />
                    <Label className="text-sm font-medium">{text.enableDiscount}</Label>
                  </div>
                  <Switch
                    checked={whatsappSettings.offer_discount_enabled}
                    onCheckedChange={(checked) => setWhatsappSettings(prev => ({ ...prev, offer_discount_enabled: checked }))}
                  />
                </div>

                {whatsappSettings.offer_discount_enabled && (
                  <div className="space-y-4 ps-4 border-s-2 border-pink-200 dark:border-pink-800">
                    {/* Discount type radio */}
                    <div className="space-y-1.5">
                      <Label className="text-sm">{text.discountType}</Label>
                      <div className="grid grid-cols-2 gap-3">
                        <button
                          type="button"
                          onClick={() => setWhatsappSettings(prev => ({ ...prev, offer_discount_type: 'percentage' }))}
                          className={`p-2.5 border-2 rounded-xl text-center transition-all ${
                            whatsappSettings.offer_discount_type === 'percentage'
                              ? 'border-pink-500 bg-pink-50 dark:bg-pink-950/20'
                              : 'border-muted hover:border-muted-foreground/30'
                          }`}
                        >
                          <Percent className="h-4 w-4 mx-auto mb-1 text-pink-600" />
                          <span className="text-xs font-medium">{text.percentage}</span>
                        </button>
                        <button
                          type="button"
                          onClick={() => setWhatsappSettings(prev => ({ ...prev, offer_discount_type: 'fixed' }))}
                          className={`p-2.5 border-2 rounded-xl text-center transition-all ${
                            whatsappSettings.offer_discount_type === 'fixed'
                              ? 'border-pink-500 bg-pink-50 dark:bg-pink-950/20'
                              : 'border-muted hover:border-muted-foreground/30'
                          }`}
                        >
                          <Tag className="h-4 w-4 mx-auto mb-1 text-pink-600" />
                          <span className="text-xs font-medium">{text.fixedAmount}</span>
                        </button>
                      </div>
                    </div>
                    {/* Discount value */}
                    <div className="space-y-1.5">
                      <Label className="text-sm">{text.discountValue}</Label>
                      <div className="flex items-center gap-2 max-w-[200px]">
                        <Input
                          type="number"
                          value={whatsappSettings.offer_discount_value}
                          onChange={(e) => setWhatsappSettings(prev => ({ ...prev, offer_discount_value: e.target.value }))}
                          className="h-9 text-center"
                          min="0"
                        />
                        <span className="text-sm font-medium text-muted-foreground whitespace-nowrap">
                          {whatsappSettings.offer_discount_type === 'percentage' ? '%' : 'DZD'}
                        </span>
                      </div>
                    </div>
                  </div>
                )}

                {/* Free shipping toggle */}
                <div className="flex items-center justify-between p-4 bg-muted/50 rounded-xl">
                  <div className="flex items-center gap-3">
                    <Truck className="h-4 w-4 text-blue-500" />
                    <div>
                      <Label className="text-sm font-medium">{text.freeShipping}</Label>
                      <p className="text-xs text-muted-foreground">{text.freeShippingDesc}</p>
                    </div>
                  </div>
                  <Switch
                    checked={whatsappSettings.offer_free_shipping}
                    onCheckedChange={(checked) => setWhatsappSettings(prev => ({ ...prev, offer_free_shipping: checked }))}
                  />
                </div>
              </CardContent>
            </Card>
          )}

          {/* Message Templates — WEBI-style with rich editor + phone preview */}
          {whatsappSettings.whatsapp_enabled && (() => {
            const msgKey = `whatsapp_message_${messageLang}`;
            const currentMessage = whatsappSettings[msgKey] || '';
            const textareaRef = React.createRef();

            const insertText = (before, after = '') => {
              const el = textareaRef.current;
              if (!el) {
                setWhatsappSettings(prev => ({ ...prev, [msgKey]: prev[msgKey] + before }));
                return;
              }
              const start = el.selectionStart;
              const end = el.selectionEnd;
              const text = currentMessage;
              const newText = text.substring(0, start) + before + text.substring(start, end) + after + text.substring(end);
              setWhatsappSettings(prev => ({ ...prev, [msgKey]: newText }));
            };

            const wrapSelection = (wrapper) => {
              const el = textareaRef.current;
              if (!el) return;
              const start = el.selectionStart;
              const end = el.selectionEnd;
              if (start === end) return;
              const text = currentMessage;
              const selected = text.substring(start, end);
              const newText = text.substring(0, start) + wrapper + selected + wrapper + text.substring(end);
              setWhatsappSettings(prev => ({ ...prev, [msgKey]: newText }));
            };

            const variables = [
              { tag: '{name}', label: text.varName },
              { tag: '{link}', label: text.varRecover },
              { tag: '{checkout_id}', label: text.varAbandoned },
              { tag: '{total}', label: text.varTotal },
              { tag: '{new_total}', label: text.varNewTotal },
              { tag: '{discount}', label: text.varDiscount },
              { tag: '{items_count}', label: text.varItems },
            ];

            // Build preview message
            const previewMsg = currentMessage
              .replace('{name}', 'أحمد')
              .replace('{link}', (whatsappSettings.store_url || 'https://agroyousfi.dz') + '/recover/ac_AB12F3')
              .replace('{checkout_id}', '#AB12F3')
              .replace('{total}', '5,200 د.ج')
              .replace('{new_total}', '4,680 د.ج')
              .replace('{discount}', '10% + شحن مجاني')
              .replace('{items_count}', '3');

            return (
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Smartphone className="h-5 w-5 text-purple-600" />
                    {text.messageTemplates}
                  </CardTitle>
                  <CardDescription>{text.messageVars}</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="flex gap-6">
                    {/* Editor side */}
                    <div className="flex-1 space-y-4">
                      {/* Language selector */}
                      <div className="space-y-2">
                        <Label className="text-sm">{text.editorLang}</Label>
                        <Select value={messageLang} onValueChange={setMessageLang}>
                          <SelectTrigger className="w-[180px]">
                            <SelectValue />
                          </SelectTrigger>
                          <SelectContent>
                            <SelectItem value="ar">العربية</SelectItem>
                            <SelectItem value="fr">Français</SelectItem>
                            <SelectItem value="en">English</SelectItem>
                          </SelectContent>
                        </Select>
                      </div>

                      {/* Formatting toolbar */}
                      <div className="flex items-center gap-1 p-1 bg-muted/50 rounded-lg border">
                        <Button
                          type="button" variant="ghost" size="sm"
                          className="h-8 w-8 p-0"
                          onClick={() => wrapSelection('*')}
                          title="Bold (*text*)"
                        >
                          <Bold className="h-4 w-4" />
                        </Button>
                        <Button
                          type="button" variant="ghost" size="sm"
                          className="h-8 w-8 p-0"
                          onClick={() => wrapSelection('_')}
                          title="Italic (_text_)"
                        >
                          <Italic className="h-4 w-4" />
                        </Button>
                        <Button
                          type="button" variant="ghost" size="sm"
                          className="h-8 w-8 p-0"
                          onClick={() => wrapSelection('~')}
                          title="Strikethrough (~text~)"
                        >
                          <Strikethrough className="h-4 w-4" />
                        </Button>
                        <div className="w-px h-5 bg-border mx-1" />
                        <Button
                          type="button" variant="ghost" size="sm"
                          className="h-8 px-2 text-xs"
                          onClick={() => insertText('😊')}
                        >
                          <Smile className="h-4 w-4" />
                        </Button>
                      </div>

                      {/* Textarea */}
                      <Textarea
                        ref={textareaRef}
                        value={currentMessage}
                        onChange={(e) => setWhatsappSettings(prev => ({ ...prev, [msgKey]: e.target.value }))}
                        rows={6}
                        dir={messageLang === 'ar' ? 'rtl' : 'ltr'}
                        className="font-mono text-sm"
                      />

                      {/* Variable tags — clickable */}
                      <div className="space-y-2">
                        <Label className="text-xs text-muted-foreground">{text.insertVariable}</Label>
                        <div className="flex flex-wrap gap-2">
                          {variables.map(v => (
                            <button
                              key={v.tag}
                              type="button"
                              onClick={() => insertText(v.tag)}
                              className="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 text-xs font-medium rounded-md border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-950/50 transition-colors cursor-pointer"
                            >
                              <Tag className="h-3 w-3" />
                              {v.label}
                            </button>
                          ))}
                        </div>
                      </div>
                    </div>

                    {/* Phone preview — hidden on small screens */}
                    <div className="hidden xl:block shrink-0">
                      <WhatsAppPhonePreview
                        message={previewMsg}
                        storeName="AgroYousfi"
                      />
                    </div>
                  </div>
                </CardContent>
              </Card>
            );
          })()}

          {/* Setup Guide */}
          {whatsappSettings.whatsapp_enabled && (
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Shield className="h-5 w-5 text-indigo-600" />
                  {text.apiSetupTitle}
                </CardTitle>
              </CardHeader>
              <CardContent>
                <ol className="space-y-3">
                  {(whatsappSettings.whatsapp_mode === 'green_api'
                    ? text.greenApiSetupSteps
                    : text.businessApiSetupSteps
                  ).map((step, idx) => (
                    <li key={idx} className="flex items-start gap-3">
                      <span className="flex items-center justify-center h-6 w-6 rounded-full bg-primary/10 text-primary text-xs font-bold shrink-0">
                        {idx + 1}
                      </span>
                      <span className="text-sm">{step}</span>
                    </li>
                  ))}
                </ol>
              </CardContent>
            </Card>
          )}
        </>
      )}
    </div>
  );

  // ─── Render: Taxes Section ───
  const renderTaxesSection = () => (
    <div className="space-y-6">
      {renderSectionHeader(text.taxes)}
      <Card>
        <CardHeader>
          <CardTitle>{text.taxes}</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center justify-between">
            <Label>{text.enableTax}</Label>
            <Switch
              checked={taxSettings.enableTax}
              onCheckedChange={(checked) => setTaxSettings(prev => ({ ...prev, enableTax: checked }))}
            />
          </div>
          {taxSettings.enableTax && (
            <>
              <div className="space-y-2">
                <Label>{text.taxRate}</Label>
                <Input
                  type="number"
                  value={taxSettings.taxRate}
                  onChange={(e) => setTaxSettings(prev => ({ ...prev, taxRate: parseFloat(e.target.value) }))}
                />
              </div>
              <div className="flex items-center justify-between">
                <Label>{text.taxIncluded}</Label>
                <Switch
                  checked={taxSettings.taxIncludedInPrice}
                  onCheckedChange={(checked) => setTaxSettings(prev => ({ ...prev, taxIncludedInPrice: checked }))}
                />
              </div>
            </>
          )}
        </CardContent>
      </Card>
    </div>
  );

  // ─── Render: Notifications Section ───
  const renderNotificationsSection = () => (
    <div className="space-y-6">
      {renderSectionHeader(text.notifications)}
      <Card>
        <CardHeader>
          <CardTitle>{text.notifications}</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="flex items-center justify-between">
            <Label>{text.emailOnNewOrder}</Label>
            <Switch
              checked={notificationSettings.emailOnNewOrder}
              onCheckedChange={(checked) => setNotificationSettings(prev => ({ ...prev, emailOnNewOrder: checked }))}
            />
          </div>
          <div className="flex items-center justify-between">
            <Label>{text.emailOnLowStock}</Label>
            <Switch
              checked={notificationSettings.emailOnLowStock}
              onCheckedChange={(checked) => setNotificationSettings(prev => ({ ...prev, emailOnLowStock: checked }))}
            />
          </div>
          <div className="flex items-center justify-between">
            <Label>{text.smsOnNewOrder}</Label>
            <Switch
              checked={notificationSettings.smsOnNewOrder}
              onCheckedChange={(checked) => setNotificationSettings(prev => ({ ...prev, smsOnNewOrder: checked }))}
            />
          </div>
          <div className="space-y-2">
            <Label>{text.lowStockThreshold}</Label>
            <Input
              type="number"
              value={notificationSettings.lowStockThreshold}
              onChange={(e) => setNotificationSettings(prev => ({ ...prev, lowStockThreshold: parseInt(e.target.value) }))}
            />
          </div>
        </CardContent>
      </Card>
    </div>
  );

  // ─── Render: Users Section ───
  const renderUsersSection = () => (
    <div className="space-y-6">
      {renderSectionHeader(text.users)}
      <Card>
        <CardHeader>
          <CardTitle>{text.users}</CardTitle>
          <CardDescription>
            {language === 'ar' ? 'إدارة صلاحيات المستخدمين' : language === 'fr' ? 'Gestion des permissions utilisateurs' : 'Manage user permissions'}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="p-4 border rounded-lg">
              <div className="flex items-center gap-3 mb-3">
                <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                  <Shield className="h-5 w-5 text-primary" />
                </div>
                <div>
                  <h3 className="font-semibold">{text.admin}</h3>
                  <p className="text-sm text-muted-foreground">{text.adminDesc}</p>
                </div>
              </div>
              <ul className="text-sm space-y-1 text-muted-foreground">
                <li>- {language === 'ar' ? 'إدارة المنتجات' : 'Manage products'}</li>
                <li>- {language === 'ar' ? 'إدارة الطلبات' : 'Manage orders'}</li>
                <li>- {language === 'ar' ? 'إدارة العملاء' : 'Manage customers'}</li>
                <li>- {language === 'ar' ? 'إدارة الإعدادات' : 'Manage settings'}</li>
                <li>- {language === 'ar' ? 'عرض التقارير' : 'View reports'}</li>
              </ul>
            </div>
            <div className="p-4 border rounded-lg">
              <div className="flex items-center gap-3 mb-3">
                <div className="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                  <Users className="h-5 w-5 text-blue-600" />
                </div>
                <div>
                  <h3 className="font-semibold">{text.staff}</h3>
                  <p className="text-sm text-muted-foreground">{text.staffDesc}</p>
                </div>
              </div>
              <ul className="text-sm space-y-1 text-muted-foreground">
                <li>- {language === 'ar' ? 'عرض المنتجات' : 'View products'}</li>
                <li>- {language === 'ar' ? 'إدارة الطلبات' : 'Manage orders'}</li>
                <li>- {language === 'ar' ? 'عرض العملاء' : 'View customers'}</li>
                <li className="text-red-500">- {language === 'ar' ? 'لا يمكن تعديل الإعدادات' : 'Cannot edit settings'}</li>
              </ul>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );

  return (
    <div className="space-y-6">
      {!activeSection && renderDashboard()}
      {activeSection === 'store' && renderStoreSection()}
      {activeSection === 'marketing' && renderMarketingSection()}
      {activeSection === 'taxes' && renderTaxesSection()}
      {activeSection === 'notifications' && renderNotificationsSection()}
      {activeSection === 'users' && renderUsersSection()}
    </div>
  );
};

export default SettingsPage;
