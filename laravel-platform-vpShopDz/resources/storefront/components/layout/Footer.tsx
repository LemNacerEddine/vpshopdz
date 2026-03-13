import React from 'react';
import { Link } from 'react-router-dom';
import { useStore } from '../../contexts/StoreContext';
import { useTheme } from '../../contexts/ThemeContext';
import { useLanguage } from '../../contexts/LanguageContext';
import { getImageUrl } from '../../lib/utils';
import {
  Facebook, Instagram, Twitter, Phone, Mail, MapPin,
  MessageCircle, Send
} from 'lucide-react';

interface FooterProps {
  style?: 'default' | 'minimal' | 'rich';
}

export const Footer: React.FC<FooterProps> = ({ style = 'default' }) => {
  const { storeName, storeLogo, socialLinks, contactInfo } = useStore();
  const { colors } = useTheme();
  const { t, isRTL } = useLanguage();

  const quickLinks = [
    { href: '/', label: t('nav.home') },
    { href: '/products', label: t('nav.products') },
    { href: '/products?deals=true', label: t('nav.deals') },
  ];

  const socialIcons = [
    { key: 'facebook', url: socialLinks.facebook, icon: Facebook },
    { key: 'instagram', url: socialLinks.instagram, icon: Instagram },
    { key: 'twitter', url: socialLinks.twitter, icon: Twitter },
    { key: 'whatsapp', url: socialLinks.whatsapp ? `https://wa.me/${socialLinks.whatsapp}` : '', icon: MessageCircle },
    { key: 'tiktok', url: socialLinks.tiktok, icon: Send },
  ].filter(s => s.url);

  if (style === 'minimal') {
    return (
      <footer style={{ backgroundColor: colors.footerBg, color: colors.footerText }}>
        <div className="container mx-auto px-4 py-6">
          <div className="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div className="flex items-center gap-2">
              {storeLogo ? (
                <img src={getImageUrl(storeLogo)} alt={storeName} className="h-8 w-8 rounded-full object-cover" />
              ) : (
                <div className="h-8 w-8 rounded-full flex items-center justify-center text-white font-bold" style={{ backgroundColor: colors.primary }}>
                  {storeName.charAt(0)}
                </div>
              )}
              <span className="font-semibold">{storeName}</span>
            </div>

            <div className="flex items-center gap-3">
              {socialIcons.map((social) => (
                <a
                  key={social.key}
                  href={social.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="h-8 w-8 rounded-full flex items-center justify-center transition-opacity hover:opacity-80"
                  style={{ backgroundColor: `${colors.footerText}15` }}
                >
                  <social.icon className="h-4 w-4" />
                </a>
              ))}
            </div>

            <p className="text-xs opacity-60">
              &copy; {new Date().getFullYear()} {storeName}. {t('footer.rights')}
            </p>
          </div>
        </div>
        <div className="text-center py-2 text-xs opacity-40" style={{ borderTop: `1px solid ${colors.footerText}15` }}>
          {t('footer.poweredBy')}
        </div>
      </footer>
    );
  }

  if (style === 'rich') {
    return (
      <footer style={{ backgroundColor: colors.footerBg, color: colors.footerText }}>
        {/* Newsletter Section */}
        <div className="border-b" style={{ borderColor: `${colors.footerText}15` }}>
          <div className="container mx-auto px-4 py-8">
            <div className="max-w-xl mx-auto text-center">
              <h3 className="text-xl font-bold mb-2">
                {isRTL ? 'اشترك في نشرتنا البريدية' : 'Subscribe to our newsletter'}
              </h3>
              <p className="text-sm opacity-70 mb-4">
                {isRTL ? 'احصل على آخر العروض والمنتجات الجديدة' : 'Get the latest offers and new products'}
              </p>
              <form className="flex gap-2">
                <input
                  type="email"
                  placeholder={isRTL ? 'بريدك الإلكتروني' : 'Your email'}
                  className="flex-1 h-11 px-4 rounded-lg text-sm bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:border-white/40"
                />
                <button
                  type="submit"
                  className="h-11 px-6 rounded-lg text-sm font-medium text-white transition-opacity hover:opacity-90"
                  style={{ backgroundColor: colors.primary }}
                >
                  {isRTL ? 'اشترك' : 'Subscribe'}
                </button>
              </form>
            </div>
          </div>
        </div>

        {/* Main Footer */}
        <div className="container mx-auto px-4 py-12">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {/* About */}
            <div>
              <div className="flex items-center gap-3 mb-4">
                {storeLogo ? (
                  <img src={getImageUrl(storeLogo)} alt={storeName} className="h-12 w-12 rounded-full object-cover bg-white p-0.5" />
                ) : (
                  <div className="h-12 w-12 rounded-full flex items-center justify-center text-white font-bold text-xl" style={{ backgroundColor: colors.primary }}>
                    {storeName.charAt(0)}
                  </div>
                )}
                <span className="font-bold text-lg">{storeName}</span>
              </div>
              <p className="text-sm opacity-70 leading-relaxed">{t('footer.aboutText')}</p>
            </div>

            {/* Quick Links */}
            <div>
              <h3 className="font-bold text-lg mb-4">{t('footer.quickLinks')}</h3>
              <ul className="space-y-2">
                {quickLinks.map((link) => (
                  <li key={link.href}>
                    <Link to={link.href} className="text-sm opacity-70 hover:opacity-100 transition-opacity">
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
                {contactInfo.phone && (
                  <li className="flex items-center gap-3 text-sm opacity-70">
                    <Phone className="h-4 w-4 shrink-0" />
                    <span dir="ltr">{contactInfo.phone}</span>
                  </li>
                )}
                {contactInfo.email && (
                  <li className="flex items-center gap-3 text-sm opacity-70">
                    <Mail className="h-4 w-4 shrink-0" />
                    <span>{contactInfo.email}</span>
                  </li>
                )}
                {contactInfo.address && (
                  <li className="flex items-start gap-3 text-sm opacity-70">
                    <MapPin className="h-4 w-4 shrink-0 mt-0.5" />
                    <span>{contactInfo.address}</span>
                  </li>
                )}
              </ul>
            </div>

            {/* Social */}
            <div>
              <h3 className="font-bold text-lg mb-4">{t('footer.followUs')}</h3>
              <div className="flex flex-wrap gap-3">
                {socialIcons.map((social) => (
                  <a
                    key={social.key}
                    href={social.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="h-10 w-10 rounded-full flex items-center justify-center transition-opacity hover:opacity-80"
                    style={{ backgroundColor: `${colors.footerText}15` }}
                  >
                    <social.icon className="h-5 w-5" />
                  </a>
                ))}
              </div>
            </div>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="border-t" style={{ borderColor: `${colors.footerText}15` }}>
          <div className="container mx-auto px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p className="text-xs opacity-50">
              &copy; {new Date().getFullYear()} {storeName}. {t('footer.rights')}
            </p>
            <p className="text-xs opacity-40">{t('footer.poweredBy')}</p>
          </div>
        </div>
      </footer>
    );
  }

  // Default footer
  return (
    <footer style={{ backgroundColor: colors.footerBg, color: colors.footerText }}>
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {/* About */}
          <div>
            <div className="flex items-center gap-3 mb-4">
              {storeLogo ? (
                <img src={getImageUrl(storeLogo)} alt={storeName} className="h-14 w-14 rounded-full bg-white p-1 object-cover" />
              ) : (
                <div className="h-14 w-14 rounded-full flex items-center justify-center text-white font-bold text-xl" style={{ backgroundColor: colors.primary }}>
                  {storeName.charAt(0)}
                </div>
              )}
              <span className="font-bold text-xl">{storeName}</span>
            </div>
            <p className="text-sm opacity-80 leading-relaxed">{t('footer.aboutText')}</p>
          </div>

          {/* Quick Links */}
          <div>
            <h3 className="font-bold text-lg mb-4">{t('footer.quickLinks')}</h3>
            <ul className="space-y-2">
              {quickLinks.map((link) => (
                <li key={link.href}>
                  <Link to={link.href} className="text-sm opacity-80 hover:opacity-100 transition-opacity">
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
              {contactInfo.phone && (
                <li className="flex items-center gap-3 text-sm opacity-80">
                  <Phone className="h-4 w-4 shrink-0" />
                  <span dir="ltr">{contactInfo.phone}</span>
                </li>
              )}
              {contactInfo.email && (
                <li className="flex items-center gap-3 text-sm opacity-80">
                  <Mail className="h-4 w-4 shrink-0" />
                  <span>{contactInfo.email}</span>
                </li>
              )}
              {contactInfo.address && (
                <li className="flex items-start gap-3 text-sm opacity-80">
                  <MapPin className="h-4 w-4 shrink-0 mt-0.5" />
                  <span>{contactInfo.address}</span>
                </li>
              )}
            </ul>
          </div>

          {/* Social */}
          <div>
            <h3 className="font-bold text-lg mb-4">{t('footer.followUs')}</h3>
            <div className="flex gap-3">
              {socialIcons.map((social) => (
                <a
                  key={social.key}
                  href={social.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="h-10 w-10 rounded-full flex items-center justify-center transition-opacity hover:opacity-80"
                  style={{ backgroundColor: `${colors.footerText}10` }}
                >
                  <social.icon className="h-5 w-5" />
                </a>
              ))}
            </div>
          </div>
        </div>

        {/* Bottom Bar */}
        <div className="mt-12 pt-8 text-center text-sm opacity-60" style={{ borderTop: `1px solid ${colors.footerText}20` }}>
          <p>&copy; {new Date().getFullYear()} {storeName}. {t('footer.rights')}</p>
          <p className="mt-1 text-xs opacity-60">{t('footer.poweredBy')}</p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
