import { Link } from "react-router-dom";
import { 
  Heart, 
  Brain, 
  Users, 
  MessageCircle, 
  Baby, 
  Zap,
  BookOpen,
  Calendar,
  Clock,
  CheckCircle
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import Navigation from "@/components/Navigation";
import Footer from "@/components/Footer";

const Services = () => {
  const services = [
    {
      icon: Heart,
      title: "زوج‌درمانی",
      description: "بهبود روابط زناشویی و حل تعارضات زوجین",
      details: [
        "درمان مشکلات ارتباطی زوجین",
        "حل تعارضات و بحران‌های زناشویی", 
        "بهبود صمیمیت عاطفی و جنسی",
        "آمادگی برای ازدواج (مشاوره پیش از ازدواج)"
      ],
      duration: "۱۰-۱۵ جلسه",
      price: "۵۰۰,۰۰۰ تومان"
    },
    {
      icon: Brain,
      title: "روانکاوی",
      description: "درمان مشکلات عمیق روانی از طریق تحلیل ناخودآگاه",
      details: [
        "شناخت عمیق خود و الگوهای رفتاری",
        "حل مشکلات ریشه‌ای شخصیت",
        "درمان ترومای گذشته",
        "تقویت بینش و خودآگاهی"
      ],
      duration: "۲۰-۳۰ جلسه",
      price: "۶۰۰,۰۰۰ تومان"
    },
    {
      icon: Users,
      title: "مشاوره خانواده",
      description: "بهبود ارتباطات خانوادگی و حل مشکلات بین نسلی",
      details: [
        "بهبود ارتباط والدین و فرزندان",
        "حل تعارضات خانوادگی",
        "مدیریت بحران‌های خانوادگی",
        "تقویت پیوندهای عاطفی خانواده"
      ],
      duration: "۸-۱۲ جلسه",
      price: "۴۵۰,۰۰۰ تومان"
    },
    {
      icon: MessageCircle,
      title: "درمان افسردگی و اضطراب",
      description: "درمان تخصصی اختلالات خلقی با روش‌های مدرن",
      details: [
        "درمان انواع افسردگی",
        "کنترل و مدیریت اضطراب",
        "درمان حملات پانیک",
        "تکنیک‌های آرام‌سازی و مدیتیشن"
      ],
      duration: "۱۲-۲۰ جلسه",
      price: "۴۰۰,۰۰۰ تومان"
    },
    {
      icon: Baby,
      title: "مشاوره نوجوان و کودک",
      description: "مشاوره تخصصی برای کودکان و نوجوانان",
      details: [
        "مشکلات رفتاری کودکان",
        "مشاوره دوران بلوغ",
        "مسائل تحصیلی و اجتماعی",
        "اضطراب جدایی و مدرسه‌هراسی"
      ],
      duration: "۶-۱۰ جلسه",
      price: "۳۵۰,۰۰۰ تومان"
    },
    {
      icon: Zap,
      title: "سکس‌تراپی",
      description: "درمان مشکلات جنسی زوجین",
      details: [
        "درمان اختلالات عملکرد جنسی",
        "بهبود صمیمیت جنسی زوجین",
        "آموزش تکنیک‌های ارتباط جنسی",
        "حل مشکلات روانی مرتبط با جنسیت"
      ],
      duration: "۸-۱۵ جلسه",
      price: "۷۰۰,۰۰۰ تومان"
    },
    {
      icon: BookOpen,
      title: "تست‌های روانشناختی",
      description: "انجام تست‌های شخصیت، هوش و تشخیصی",
      details: [
        "تست شخصیت MBTI",
        "تست هوش IQ",
        "تست تشخیص ADHD",
        "ارزیابی اختلالات یادگیری"
      ],
      duration: "۲-۳ جلسه",
      price: "۳۰۰,۰۰۰ تومان"
    },
    {
      icon: MessageCircle,
      title: "مشاوره آنلاین",
      description: "ارائه خدمات مشاوره از راه دور",
      details: [
        "مشاوره از طریق ویدیوکال",
        "انعطاف در ساعات کاری",
        "مناسب برای ساکنین سایر شهرها",
        "حفظ حریم خصوصی بیشتر"
      ],
      duration: "مطابق نوع مشاوره",
      price: "۲۰% تخفیف نسبت به حضوری"
    }
  ];

  const processSteps = [
    {
      step: "۱",
      title: "تماس اولیه",
      description: "رزرو وقت و مشاوره اولیه تلفنی"
    },
    {
      step: "۲", 
      title: "جلسه ارزیابی",
      description: "بررسی دقیق مشکلات و تعیین برنامه درمان"
    },
    {
      step: "۳",
      title: "شروع درمان",
      description: "اجرای برنامه درمانی مطابق نیاز مراجع"
    },
    {
      step: "۴",
      title: "پیگیری",
      description: "ارزیابی پیشرفت و تنظیم برنامه در صورت نیاز"
    }
  ];

  return (
    <div className="min-h-screen bg-background">
      <Navigation />
      
      {/* Hero Section */}
      <section className="py-20 bg-gradient-hero text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h1 className="text-4xl md:text-5xl font-bold mb-6">خدمات تخصصی روانشناسی</h1>
            <p className="text-xl text-blue-100 max-w-3xl mx-auto persian-text">
              ارائه جامع‌ترین خدمات روانشناسی و مشاوره با جدیدترین روش‌های علمی
            </p>
          </div>
        </div>
      </section>

      {/* Services Grid */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {services.map((service, index) => (
              <Card key={index} className="text-right hover:shadow-lg transition-shadow">
                <CardHeader>
                  <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
                    <service.icon className="w-8 h-8 text-primary" />
                  </div>
                  <CardTitle className="text-xl">{service.title}</CardTitle>
                  <CardDescription className="persian-text">{service.description}</CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <ul className="space-y-2">
                    {service.details.map((detail, idx) => (
                      <li key={idx} className="flex items-center space-x-2 space-x-reverse text-sm">
                        <CheckCircle className="w-4 h-4 text-primary flex-shrink-0" />
                        <span>{detail}</span>
                      </li>
                    ))}
                  </ul>
                  <div className="border-t pt-4 space-y-2">
                    <div className="flex justify-between items-center">
                      <span className="text-sm text-muted-foreground">مدت درمان:</span>
                      <span className="text-sm font-medium">{service.duration}</span>
                    </div>
                    <div className="flex justify-between items-center">
                      <span className="text-sm text-muted-foreground">هزینه هر جلسه:</span>
                      <span className="text-sm font-bold text-primary">{service.price}</span>
                    </div>
                  </div>
                  <Button className="w-full" asChild>
                    <Link to="/appointment">رزرو وقت</Link>
                  </Button>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* Process Section */}
      <section className="py-20 bg-muted/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-foreground">فرآیند درمان</h2>
            <p className="text-xl text-muted-foreground">مراحل ارائه خدمات از اولین تماس تا پایان درمان</p>
          </div>
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            {processSteps.map((step, index) => (
              <div key={index} className="text-center">
                <div className="w-16 h-16 bg-primary rounded-full flex items-center justify-center mx-auto mb-4">
                  <span className="text-2xl font-bold text-white">{step.step}</span>
                </div>
                <h3 className="text-xl font-semibold mb-2">{step.title}</h3>
                <p className="text-muted-foreground persian-text">{step.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* FAQ Section */}
      <section className="py-20 bg-white">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-foreground">سوالات متداول</h2>
            <p className="text-xl text-muted-foreground">پاسخ به رایج‌ترین سوالات در مورد خدمات ما</p>
          </div>
          <div className="space-y-6">
            {[
              {
                question: "چه زمانی باید به روانشناس مراجعه کنم؟",
                answer: "زمانی که احساس می‌کنید مشکلات عاطفی یا روانی بر زندگی روزمره‌تان تأثیر گذاشته و نمی‌توانید به تنهایی با آن‌ها کنار بیایید."
              },
              {
                question: "مدت زمان درمان چقدر است؟",
                answer: "مدت درمان بسته به نوع مشکل و شدت آن متفاوت است. معمولاً بین ۶ تا ۳۰ جلسه در نظر گرفته می‌شود."
              },
              {
                question: "آیا اطلاعات من محرمانه خواهد ماند؟",
                answer: "بله، حفظ اسرار مراجعین یکی از اصول اخلاقی مهم روانشناسی است و تمام اطلاعات شما کاملاً محرمانه خواهد بود."
              },
              {
                question: "آیا امکان مشاوره آنلاین وجود دارد؟",
                answer: "بله، خدمات مشاوره آنلاین با کیفیت مشابه جلسات حضوری ارائه می‌شود و برای افرادی که امکان حضور ندارند مناسب است."
              }
            ].map((faq, index) => (
              <Card key={index} className="text-right">
                <CardHeader>
                  <CardTitle className="text-lg">{faq.question}</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-muted-foreground persian-text">{faq.answer}</p>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-gradient-hero text-white">
        <div className="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl md:text-4xl font-bold mb-6">آماده دریافت خدمات هستید؟</h2>
          <p className="text-xl mb-8 text-blue-100 persian-text">
            همین الان جلسه مشاوره خود را رزرو کنید و اولین قدم را برای بهبود زندگی‌تان بردارید
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button size="lg" variant="secondary" asChild className="text-lg px-8 py-4">
              <Link to="/appointment" className="flex items-center space-x-2 space-x-reverse">
                <Calendar className="w-6 h-6" />
                <span>رزرو وقت مشاوره</span>
              </Link>
            </Button>
            <Button size="lg" variant="outline" asChild className="text-lg px-8 py-4 text-white border-white hover:bg-white hover:text-primary">
              <Link to="/contact" className="flex items-center space-x-2 space-x-reverse">
                <MessageCircle className="w-6 h-6" />
                <span>مشاوره رایگان</span>
              </Link>
            </Button>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  );
};

export default Services;