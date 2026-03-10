import React from 'react';
import { Link } from 'react-router-dom';
import { useLanguage } from '@/contexts/LanguageContext';
import { useStoreSettings } from '@/contexts/StoreSettingsContext';
import { Facebook, Instagram, Twitter, Phone, Mail, MapPin } from 'lucide-react';

const DEFAULT_LOGO = "https://customer-assets.emergentagent.com/job_cb33075f-a467-40a3-8ccf-6a7d58e2dd7b/artifacts/9ov58a7g_548325177_122096850867034427_2184721735778021830_n.jpg";

export const Footer = () => {
  const { t, isRTL } = useLanguage();
  const { storeInfo } = useStoreSettings();

  const logoUrl = storeInfo.store_logo || DEFAULT_LOGO;
  const storeName = storeInfo.store_name || 'AgroYousfi';
  const storePhone = storeInfo.store_phone || '+213 XX XX XX XX';
  const storeEmail = storeInfo.store_email || 'contact@agroyousfi.dz';
  const storeAddress = storeInfo.store_address || 'الجزائر';
  const facebookUrl = storeInfo.store_facebook || '#';
  const instagramUrl = storeInfo.store_instagram || '#';

  const quickLinks = [
    { href: '/', label: t('nav.home') },
    { href: '/products', label: t('nav.products') },
    { href: '/categories', label: t('nav.categories') },
    { href: '/login', label: t('nav.login') },
  ];

  return (
    <footer className="bg-primary text-primary-foreground mt-auto">
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {/* About */}
          <div>
            <Link to="/" className="flex items-center gap-3 mb-4">
              <img
                src={logoUrl}
                alt={storeName}
                className="h-14 w-14 rounded-full bg-white p-1 object-cover"
              />
              <span className="font-bold text-xl">{storeName}</span>
            </Link>
            <p className="text-primary-foreground/80 text-sm leading-relaxed">
              {t('footer.aboutText')}
            </p>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="font-bold text-lg mb-4">{t('footer.quickLinks')}</h3>
            <ul className="space-y-2">
              {quickLinks.map((link) => (
                <li key={link.href}>
                  <Link 
                    to={link.href}
                    className="text-primary-foreground/80 hover:text-primary-foreground transition-colors text-sm"
                  >
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          {/* Contact */}
          <div>
            <h3 className="font-bold text-lg mb-4">{t('footer.contact')}</h3>
            <ul className="space-y-3">
              <li className="flex items-center gap-3 text-sm text-primary-foreground/80">
                <Phone className="h-4 w-4 shrink-0" />
                <span dir="ltr">{storePhone}</span>
              </li>
              <li className="flex items-center gap-3 text-sm text-primary-foreground/80">
                <Mail className="h-4 w-4 shrink-0" />
                <span>{storeEmail}</span>
              </li>
              <li className="flex items-start gap-3 text-sm text-primary-foreground/80">
                <MapPin className="h-4 w-4 shrink-0 mt-0.5" />
                <span>{storeAddress}</span>
              </li>
            </ul>
          </div>

          {/* Social */}
          <div>
            <h3 className="font-bold text-lg mb-4">{t('footer.followUs')}</h3>
            <div className="flex gap-3">
              <a
                href={facebookUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="h-10 w-10 rounded-full bg-primary-foreground/10 hover:bg-primary-foreground/20 flex items-center justify-center transition-colors"
                aria-label="Facebook"
              >
                <Facebook className="h-5 w-5" />
              </a>
              <a
                href={instagramUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="h-10 w-10 rounded-full bg-primary-foreground/10 hover:bg-primary-foreground/20 flex items-center justify-center transition-colors"
                aria-label="Instagram"
              >
                <Instagram className="h-5 w-5" />
              </a>
            </div>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="mt-12 pt-8 border-t border-primary-foreground/20 text-center text-sm text-primary-foreground/60">
          <p>© {new Date().getFullYear()} {storeName}. {t('footer.rights')}</p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
