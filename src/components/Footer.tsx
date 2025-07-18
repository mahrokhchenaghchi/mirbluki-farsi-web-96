import { Link } from "react-router-dom";
import { Phone, Mail, MapPin, Calendar, Instagram, MessageCircle } from "lucide-react";

const Footer = () => {
  return (
    <footer className="bg-foreground text-background">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          {/* Brand Section */}
          <div className="space-y-4">
            <div className="flex items-center space-x-3 space-x-reverse">
              <div className="w-12 h-12 bg-gradient-hero rounded-full flex items-center justify-center">
                <span className="text-white font-bold text-xl">ج.م</span>
              </div>
              <div>
                <h3 className="text-xl font-bold">جواد میربلوکی</h3>
                <p className="text-background/70">روانشناس و مشاور خانواده</p>
              </div>
            </div>
            <p className="text-background/80 persian-text">
              ارائه خدمات تخصصی روانشناسی، زوج‌درمانی و مشاوره خانواده با بیش از ۱۰ سال تجربه در کرج
            </p>
          </div>

          {/* Quick Links */}
          <div className="space-y-4">
            <h4 className="text-lg font-semibold">دسترسی سریع</h4>
            <ul className="space-y-2">
              <li><Link to="/" className="text-background/80 hover:text-background transition-colors">صفحه اصلی</Link></li>
              <li><Link to="/about" className="text-background/80 hover:text-background transition-colors">درباره من</Link></li>
              <li><Link to="/services" className="text-background/80 hover:text-background transition-colors">خدمات</Link></li>
              <li><Link to="/blog" className="text-background/80 hover:text-background transition-colors">بلاگ</Link></li>
              <li><Link to="/appointment" className="text-background/80 hover:text-background transition-colors">رزرو وقت</Link></li>
            </ul>
          </div>

          {/* Services */}
          <div className="space-y-4">
            <h4 className="text-lg font-semibold">خدمات ما</h4>
            <ul className="space-y-2 text-background/80">
              <li>زوج‌درمانی</li>
              <li>سکس‌تراپی</li>
              <li>روانکاوی</li>
              <li>درمان افسردگی</li>
              <li>مشاوره نوجوان</li>
              <li>تست شخصیت و هوش</li>
            </ul>
          </div>

          {/* Contact Info */}
          <div className="space-y-4">
            <h4 className="text-lg font-semibold">اطلاعات تماس</h4>
            <div className="space-y-3">
              <div className="flex items-center space-x-3 space-x-reverse">
                <MapPin className="w-5 h-5 text-primary flex-shrink-0" />
                <span className="text-background/80 text-sm">
                  کرج، چهارراه هفت تیر، کوچه انقلاب، برج ارم، طبقه ۴، واحد ۴۰۱
                </span>
              </div>
              <div className="flex items-center space-x-3 space-x-reverse">
                <Phone className="w-5 h-5 text-primary" />
                <span className="text-background/80" dir="ltr">026-34567890</span>
              </div>
              <div className="flex items-center space-x-3 space-x-reverse">
                <Mail className="w-5 h-5 text-primary" />
                <span className="text-background/80" dir="ltr">info@javadmirbluki.com</span>
              </div>
            </div>

            {/* Social Media */}
            <div className="flex space-x-4 space-x-reverse pt-4">
              <a href="#" className="text-background/80 hover:text-primary transition-colors">
                <Instagram className="w-6 h-6" />
              </a>
              <a href="#" className="text-background/80 hover:text-primary transition-colors">
                <MessageCircle className="w-6 h-6" />
              </a>
            </div>
          </div>
        </div>

        <div className="border-t border-background/20 mt-8 pt-8 text-center">
          <div className="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <p className="text-background/70 text-sm">
              © ۱۴۰۳ - تمامی حقوق محفوظ است برای جواد میربلوکی
            </p>
            <div className="flex items-center space-x-4 space-x-reverse text-sm">
              <span className="text-background/70">دارای پروانه رسمی از سازمان نظام روانشناسی ایران</span>
              <span className="text-primary">شماره پروانه: ۱۲۳۴۵</span>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;