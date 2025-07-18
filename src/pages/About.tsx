import { Award, Users, BookOpen, Heart, CheckCircle, Star } from "lucide-react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import Navigation from "@/components/Navigation";
import Footer from "@/components/Footer";
import drImage from "@/assets/dr-javad-mirbluki.jpg";
import clinicImage from "@/assets/clinic-interior.jpg";

const About = () => {
  const qualifications = [
    {
      icon: BookOpen,
      title: "تحصیلات",
      description: "کارشناس ارشد روانشناسی، دانشگاه تهران"
    },
    {
      icon: Award,
      title: "پروانه رسمی",
      description: "شماره پروانه ۱۲۳۴۵ از سازمان نظام روانشناسی ایران"
    },
    {
      icon: Users,
      title: "عضویت‌ها",
      description: "عضو انجمن روانشناسی ایران و انجمن زوج‌درمانگران"
    },
    {
      icon: Heart,
      title: "تخصص‌ها",
      description: "زوج‌درمانی، روانکاوی، سکس‌تراپی، درمان خانواده"
    }
  ];

  const achievements = [
    "بیش از ۱۰ سال تجربه در مشاوره و روانشناسی",
    "درمان موفق بیش از ۱۰۰۰ مراجع",
    "مدرس دوره‌های تخصصی روانشناسی",
    "مشاور رسمی آموزش و پرورش استان البرز",
    "نویسنده مقالات تخصصی در زمینه روانشناسی",
    "برگزارکننده کارگاه‌های آموزشی خانواده"
  ];

  const approaches = [
    {
      title: "رویکرد شناختی-رفتاری",
      description: "استفاده از تکنیک‌های مدرن CBT برای تغییر الگوهای فکری و رفتاری"
    },
    {
      title: "روانکاوی",
      description: "بررسی عمیق ناخودآگاه و تجارب گذشته برای فهم مشکلات کنونی"
    },
    {
      title: "درمان خانواده‌محور",
      description: "درنظرگیری تمام اعضای خانواده در فرآیند درمان"
    },
    {
      title: "درمان تلفیقی",
      description: "ترکیب روش‌های مختلف متناسب با نیاز هر مراجع"
    }
  ];

  return (
    <div className="min-h-screen bg-background">
      <Navigation />
      
      {/* Hero Section */}
      <section className="py-20 bg-gradient-hero text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h1 className="text-4xl md:text-5xl font-bold mb-6">درباره دکتر جواد میربلوکی</h1>
            <p className="text-xl text-blue-100 max-w-3xl mx-auto persian-text">
              روانشناس مجرب و مشاور خانواده با تعهد به ارائه بهترین خدمات روانشناسی
            </p>
          </div>
        </div>
      </section>

      {/* Main About Section */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <div className="text-right">
              <h2 className="text-3xl md:text-4xl font-bold mb-6 text-foreground">مسیر حرفه‌ای من</h2>
              <div className="space-y-6 text-lg text-muted-foreground persian-text">
                <p>
                  به عنوان کارشناس ارشد روانشناسی با بیش از ۱۰ سال تجربه، همواره تلاش کرده‌ام تا با 
                  استفاده از جدیدترین روش‌های علمی و تجربه عملی، به بهبود کیفیت زندگی مراجعین کمک کنم.
                </p>
                <p>
                  مسیر حرفه‌ای من با تحصیل در رشته روانشناسی در دانشگاه تهران آغاز شد و با کسب 
                  پروانه رسمی از سازمان نظام روانشناسی ایران ادامه یافت. طی این سال‌ها، موفق به 
                  تخصص در زمینه‌های زوج‌درمانی، روانکاوی و مشاوره خانواده شده‌ام.
                </p>
                <p>
                  اعتقاد دارم که هر فردی ظرفیت‌های ذاتی برای بهبود و رشد دارد و وظیفه من 
                  کمک به کشف و شکوفایی این ظرفیت‌هاست. رویکرد من در درمان، ترکیبی از روش‌های 
                  علمی مدرن و درک عمیق از فرهنگ و ارزش‌های ایرانی است.
                </p>
              </div>
            </div>
            <div className="flex justify-center">
              <img 
                src={drImage} 
                alt="دکتر جواد میربلوکی" 
                className="w-full max-w-md rounded-lg shadow-xl"
              />
            </div>
          </div>
        </div>
      </section>

      {/* Qualifications */}
      <section className="py-20 bg-muted/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-foreground">مدارک و تخصص‌ها</h2>
            <p className="text-xl text-muted-foreground">تحصیلات و گواهینامه‌های تخصصی</p>
          </div>
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            {qualifications.map((qual, index) => (
              <Card key={index} className="text-center">
                <CardHeader>
                  <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <qual.icon className="w-8 h-8 text-primary" />
                  </div>
                  <CardTitle className="text-xl">{qual.title}</CardTitle>
                </CardHeader>
                <CardContent>
                  <CardDescription className="persian-text">{qual.description}</CardDescription>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* Achievements */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <div>
              <img 
                src={clinicImage} 
                alt="کلینیک روانشناسی" 
                className="rounded-lg shadow-xl w-full h-96 object-cover"
              />
            </div>
            <div className="text-right">
              <h2 className="text-3xl md:text-4xl font-bold mb-6 text-foreground">دستاوردها و افتخارات</h2>
              <p className="text-lg text-muted-foreground mb-8 persian-text">
                طی سال‌های فعالیت حرفه‌ای، توانسته‌ام رکوردهای قابل توجهی در زمینه ارائه 
                خدمات روانشناسی کسب کنم.
              </p>
              <ul className="space-y-4">
                {achievements.map((achievement, index) => (
                  <li key={index} className="flex items-center space-x-3 space-x-reverse">
                    <CheckCircle className="w-5 h-5 text-primary flex-shrink-0" />
                    <span className="text-muted-foreground">{achievement}</span>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        </div>
      </section>

      {/* Treatment Approaches */}
      <section className="py-20 bg-muted/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-foreground">رویکردهای درمانی</h2>
            <p className="text-xl text-muted-foreground max-w-3xl mx-auto persian-text">
              استفاده از متنوع‌ترین و مؤثرترین روش‌های درمانی متناسب با نیاز هر مراجع
            </p>
          </div>
          <div className="grid md:grid-cols-2 gap-8">
            {approaches.map((approach, index) => (
              <Card key={index} className="text-right">
                <CardHeader>
                  <CardTitle className="text-xl">{approach.title}</CardTitle>
                </CardHeader>
                <CardContent>
                  <CardDescription className="persian-text text-base">{approach.description}</CardDescription>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* Patient Reviews */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-foreground">نظرات مراجعین</h2>
            <p className="text-xl text-muted-foreground">تجربه واقعی مراجعین از خدمات ما</p>
          </div>
          <div className="grid md:grid-cols-3 gap-8">
            {[
              {
                name: "خانم صادقی",
                text: "بعد از ماه‌ها مشکل در زندگی زناشویی، جلسات زوج‌درمانی با دکتر میربلوکی نقطه عطفی در زندگی ما بود.",
                rating: 5
              },
              {
                name: "آقای رضایی",
                text: "روش درمان دکتر بسیار علمی و منطقی است. توانست مشکل چندساله افسردگی من را حل کند.",
                rating: 5
              },
              {
                name: "خانم احمدی",
                text: "محیط کلینیک بسیار آرام و دکتر با صبر و حوصله به مشکلات گوش می‌دهد.",
                rating: 5
              }
            ].map((review, index) => (
              <Card key={index} className="text-right">
                <CardHeader>
                  <div className="flex justify-end space-x-1 space-x-reverse mb-4">
                    {[...Array(review.rating)].map((_, i) => (
                      <Star key={i} className="w-5 h-5 fill-yellow-400 text-yellow-400" />
                    ))}
                  </div>
                  <CardDescription className="text-lg persian-text">"{review.text}"</CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="font-semibold">{review.name}</p>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      <Footer />
    </div>
  );
};

export default About;