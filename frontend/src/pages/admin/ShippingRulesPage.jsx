import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useLanguage } from '@/contexts/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import {
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger,
} from '@/components/ui/dialog';
import { Plus, Pencil, Trash2, Shield, Loader2 } from 'lucide-react';
import { toast } from 'sonner';

const API = `${process.env.REACT_APP_BACKEND_URL}/api`;

const ShippingRulesPage = () => {
  const { language } = useLanguage();
  const [rules, setRules] = useState([]);
  const [loading, setLoading] = useState(true);
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingRule, setEditingRule] = useState(null);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({
    rule_name: '', rule_type: 'min_cart_total', condition_value: '',
    shipping_cost_override: '0', is_active: true, start_date: '', end_date: '', priority: '0'
  });

  const l = {
    ar: {
      title: 'قواعد الشحن المجاني',
      addRule: 'إضافة قاعدة',
      editRule: 'تعديل القاعدة',
      ruleName: 'اسم القاعدة',
      ruleType: 'نوع القاعدة',
      conditionValue: 'قيمة الشرط',
      costOverride: 'تكلفة الشحن البديلة',
      priority: 'الأولوية',
      startDate: 'تاريخ البداية',
      endDate: 'تاريخ النهاية',
      active: 'نشطة',
      save: 'حفظ',
      delete: 'حذف',
      noRules: 'لا توجد قواعد بعد',
      types: {
        min_cart_total: 'حد أدنى للمبلغ',
        min_cart_items: 'حد أدنى للكمية',
        free_for_category: 'مجاني للفئة',
        free_for_product: 'مجاني للمنتج'
      }
    },
    fr: {
      title: 'Règles de livraison gratuite',
      addRule: 'Ajouter une règle',
      editRule: 'Modifier la règle',
      ruleName: 'Nom de la règle',
      ruleType: 'Type de règle',
      conditionValue: 'Valeur de la condition',
      costOverride: 'Coût de livraison alternatif',
      priority: 'Priorité',
      startDate: 'Date de début',
      endDate: 'Date de fin',
      active: 'Active',
      save: 'Enregistrer',
      delete: 'Supprimer',
      noRules: 'Aucune règle encore',
      types: {
        min_cart_total: 'Montant minimum',
        min_cart_items: 'Quantité minimum',
        free_for_category: 'Gratuit par catégorie',
        free_for_product: 'Gratuit par produit'
      }
    },
    en: {
      title: 'Free Shipping Rules',
      addRule: 'Add Rule',
      editRule: 'Edit Rule',
      ruleName: 'Rule Name',
      ruleType: 'Rule Type',
      conditionValue: 'Condition Value',
      costOverride: 'Shipping Cost Override',
      priority: 'Priority',
      startDate: 'Start Date',
      endDate: 'End Date',
      active: 'Active',
      save: 'Save',
      delete: 'Delete',
      noRules: 'No rules yet',
      types: {
        min_cart_total: 'Min Cart Total',
        min_cart_items: 'Min Cart Items',
        free_for_category: 'Free for Category',
        free_for_product: 'Free for Product'
      }
    }
  };
  const text = l[language] || l.ar;

  useEffect(() => { fetchRules(); }, []);

  const fetchRules = async () => {
    try {
      setLoading(true);
      const res = await axios.get(`${API}/shipping/rules`, { withCredentials: true });
      setRules(res.data);
    } catch (error) {
      console.error('Error:', error);
    } finally {
      setLoading(false);
    }
  };

  const openAdd = () => {
    setEditingRule(null);
    setForm({
      rule_name: '', rule_type: 'min_cart_total', condition_value: '',
      shipping_cost_override: '0', is_active: true, start_date: '', end_date: '', priority: '0'
    });
    setDialogOpen(true);
  };

  const openEdit = (rule) => {
    setEditingRule(rule);
    setForm({
      rule_name: rule.rule_name,
      rule_type: rule.rule_type,
      condition_value: rule.condition_value,
      shipping_cost_override: rule.shipping_cost_override?.toString() || '0',
      is_active: !!rule.is_active,
      start_date: rule.start_date ? rule.start_date.split('T')[0] : '',
      end_date: rule.end_date ? rule.end_date.split('T')[0] : '',
      priority: rule.priority?.toString() || '0'
    });
    setDialogOpen(true);
  };

  const handleSave = async () => {
    if (!form.rule_name || !form.condition_value) {
      toast.error(language === 'ar' ? 'يرجى ملء الحقول المطلوبة' : 'Fill required fields');
      return;
    }
    try {
      setSaving(true);
      const payload = {
        ...form,
        shipping_cost_override: parseFloat(form.shipping_cost_override) || 0,
        priority: parseInt(form.priority) || 0,
        start_date: form.start_date || null,
        end_date: form.end_date || null
      };

      if (editingRule) {
        await axios.put(`${API}/shipping/rules/${editingRule.rule_id}`, payload, { withCredentials: true });
      } else {
        await axios.post(`${API}/shipping/rules`, payload, { withCredentials: true });
      }
      toast.success(language === 'ar' ? 'تم الحفظ' : 'Saved');
      setDialogOpen(false);
      fetchRules();
    } catch (error) {
      toast.error(language === 'ar' ? 'خطأ' : 'Error');
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async (ruleId) => {
    if (!confirm(language === 'ar' ? 'هل أنت متأكد من الحذف؟' : 'Are you sure?')) return;
    try {
      await axios.delete(`${API}/shipping/rules/${ruleId}`, { withCredentials: true });
      toast.success(language === 'ar' ? 'تم الحذف' : 'Deleted');
      fetchRules();
    } catch (error) {
      toast.error(language === 'ar' ? 'خطأ' : 'Error');
    }
  };

  if (loading) {
    return <div className="flex items-center justify-center h-96"><Loader2 className="h-12 w-12 animate-spin text-primary" /></div>;
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">{text.title}</h1>
        <Button onClick={openAdd}><Plus className="h-4 w-4 me-2" />{text.addRule}</Button>
      </div>

      {rules.length === 0 ? (
        <Card>
          <CardContent className="py-12 text-center">
            <Shield className="h-16 w-16 mx-auto text-muted-foreground/30 mb-4" />
            <p className="text-muted-foreground">{text.noRules}</p>
          </CardContent>
        </Card>
      ) : (
        <div className="grid gap-4">
          {rules.map(rule => (
            <Card key={rule.rule_id}>
              <CardContent className="p-4">
                <div className="flex items-center justify-between">
                  <div className="flex-1">
                    <div className="flex items-center gap-2 mb-1">
                      <h3 className="font-bold">{rule.rule_name}</h3>
                      <Badge variant={rule.is_active ? 'default' : 'secondary'}>
                        {rule.is_active ? (language === 'ar' ? 'نشطة' : 'Active') : (language === 'ar' ? 'غير نشطة' : 'Inactive')}
                      </Badge>
                      <Badge variant="outline">{text.types[rule.rule_type]}</Badge>
                    </div>
                    <p className="text-sm text-muted-foreground">
                      {rule.rule_type === 'min_cart_total' && `≥ ${parseFloat(rule.condition_value).toLocaleString()} DZD`}
                      {rule.rule_type === 'min_cart_items' && `≥ ${rule.condition_value} items`}
                      {(rule.rule_type === 'free_for_category' || rule.rule_type === 'free_for_product') && rule.condition_value}
                      {rule.start_date && ` | ${rule.start_date.split('T')[0]}`}
                      {rule.end_date && ` → ${rule.end_date.split('T')[0]}`}
                    </p>
                  </div>
                  <div className="flex items-center gap-2">
                    <Button variant="ghost" size="icon" onClick={() => openEdit(rule)}>
                      <Pencil className="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="icon" className="text-destructive" onClick={() => handleDelete(rule.rule_id)}>
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      )}

      {/* Add/Edit Dialog */}
      <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{editingRule ? text.editRule : text.addRule}</DialogTitle>
          </DialogHeader>
          <div className="space-y-4">
            <div className="space-y-2">
              <Label>{text.ruleName} *</Label>
              <Input value={form.rule_name} onChange={e => setForm(p => ({...p, rule_name: e.target.value}))} />
            </div>
            <div className="space-y-2">
              <Label>{text.ruleType}</Label>
              <Select value={form.rule_type} onValueChange={v => setForm(p => ({...p, rule_type: v}))}>
                <SelectTrigger><SelectValue /></SelectTrigger>
                <SelectContent>
                  {Object.entries(text.types).map(([k, v]) => (
                    <SelectItem key={k} value={k}>{v}</SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>
            <div className="space-y-2">
              <Label>{text.conditionValue} *</Label>
              <Input value={form.condition_value} onChange={e => setForm(p => ({...p, condition_value: e.target.value}))}
                placeholder={form.rule_type === 'min_cart_total' ? '50000' : form.rule_type === 'min_cart_items' ? '5' : 'ID'} />
            </div>
            <div className="space-y-2">
              <Label>{text.costOverride}</Label>
              <Input type="number" min="0" value={form.shipping_cost_override}
                onChange={e => setForm(p => ({...p, shipping_cost_override: e.target.value}))} />
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-2">
                <Label className="text-xs">{text.startDate}</Label>
                <Input type="date" value={form.start_date} onChange={e => setForm(p => ({...p, start_date: e.target.value}))} />
              </div>
              <div className="space-y-2">
                <Label className="text-xs">{text.endDate}</Label>
                <Input type="date" value={form.end_date} onChange={e => setForm(p => ({...p, end_date: e.target.value}))} />
              </div>
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-2">
                <Label>{text.priority}</Label>
                <Input type="number" value={form.priority} onChange={e => setForm(p => ({...p, priority: e.target.value}))} />
              </div>
              <div className="flex items-center gap-2 pt-6">
                <Switch checked={form.is_active} onCheckedChange={v => setForm(p => ({...p, is_active: v}))} />
                <Label>{text.active}</Label>
              </div>
            </div>
            <Button onClick={handleSave} className="w-full" disabled={saving}>
              {saving ? <Loader2 className="h-4 w-4 me-2 animate-spin" /> : null}
              {text.save}
            </Button>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
};

export default ShippingRulesPage;
