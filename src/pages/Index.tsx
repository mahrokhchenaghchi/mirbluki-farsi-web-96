import { Link } from "react-router-dom";
import { 
  Calendar, 
  Heart, 
  Brain, 
  Users, 
  MessageCircle, 
  Award, 
  Star,
  ArrowLeft,
  CheckCircle,
  Phone
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import Navigation from "@/components/Navigation";
import Footer from "@/components/Footer";
import drImage from "@/assets/dr-javad-mirbluki.jpg";
import persianBg from "@/assets/persian-pattern-bg.jpg";
import clinicImage from "@/assets/clinic-interior.jpg";

const Index = () => {
  const services = [
    {
      icon: Heart,
      title: "زوج‌درمانی",
      description: "بهبود روابط زناشویی و حل تعارضات زوجین با روش‌های علمی و تجربه‌محور"
    },
    {
      icon: Brain,
      title: "روانکاوی",
      description: "درمان مشکلات عمیق روانی و شناخت خود از طریق تحلیل ناخودآگاه"
    },
    {
      icon: Users,
      title: "مشاوره خانواده",
      description: "بهبود ارتباطات خانوادگی و حل مشکلات بین نسلی"
    },
    {
      icon: MessageCircle,
      title: "درمان افسردگی و اضطراب",
      description: "درمان تخصصی اختلالات خلقی با جدیدترین روش‌های علمی"
    }
  ];

  const stats = [
    { number: "۱۰+", label: "سال تجربه" },
    { number: "۱۰۰۰+", label: "مراجع موفق" },
    { number: "۹۵٪", label: "رضایت مراجعین" },
    { number: "۲۴/۷", label: "پشتیبانی" }
  ];

  const testimonials = [
    {
      name: "خانم احمدی",
      text: "دکتر میربلوکی با صبر و حوصله زیادی به مشکلات ما گوش داد و راه‌حل‌های عملی ارائه کرد.",
      rating: 5
    },
    {
      name: "آقای حسینی",
      text: "بعد از جلسات زوج‌درمانی، رابطه‌مان به طور قابل توجهی بهبود یافت.",
      rating: 5
    },
    {
      name: "خانم کریمی",
      text: "روش درمان ایشان بسیار حرفه‌ای و مؤثر است. از جلسات بسیار راضی هستم.",
      rating: 5
    }
  ];

  const blogPosts = [
    {
      title: "چگونه با استرس مقابله کنیم؟",
      excerpt: "راهکارهای عملی برای مدیریت استرس در زندگی روزمره",
      image: clinicImage,
      date: "۱۵ دی ۱۴۰۳"
    },
    {
      title: "رازهای رابطه سالم",
      excerpt: "اصول اساسی برای داشتن رابطه عاطفی پایدار و سالم",
      image: clinicImage,
      date: "۱۰ دی ۱۴۰۳"
    },
    {
      title: "نقش روانکاوی در درمان",
      excerpt: "چگونه روانکاوی می‌تواند به حل مشکلات عمیق روانی کمک کند",
      image: clinicImage,
      date: "۵ دی ۱۴۰۳"
    }
  ];

  return (
    <div className="min-h-screen bg-background">
      <Navigation />
      
      {/* Hero Section */}
      <section 
        className="relative min-h-screen flex items-center bg-gradient-hero overflow-hidden"
        style={{
          backgroundImage: `linear-gradient(rgba(33, 150, 243, 0.9), rgba(33, 150, 243, 0.7)), url(${persianBg})`,
          backgroundSize: 'cover',
          backgroundPosition: 'center'
        }}
      >
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
          <div className="grid lg:grid-cols-2 gap-12 items-center">
            <div className="text-right text-white animate-fade-in-up">
              <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 persian-text">
                روانشناس و مشاور خانواده
                <br />
                <span className="text-yellow-300">جواد میربلوکی</span>
              </h1>
              <p className="text-xl md:text-2xl mb-8 text-blue-100 persian-text">
                رویکرد تخصصی در درمان زوج‌ها، روانکاوی، و مشاوره فردی
              </p>
              <p className="text-lg mb-8 text-blue-100 persian-text">
                با بیش از ۱۰ سال تجربه در زمینه روانشناسی بالینی و دارای پروانه رسمی از سازمان نظام روانشناسی ایران
              </p>
              <div className="flex flex-col sm:flex-row gap-4">
                <Button size="lg" variant="secondary" asChild className="text-lg px-8 py-4">
                  <Link to="/appointment" className="flex items-center space-x-2 space-x-reverse">
                    <Calendar className="w-6 h-6" />
                    <span>رزرو وقت مشاوره</span>
                  </Link>
                </Button>
                <Button size="lg" variant="outline" asChild className="text-lg px-8 py-4 text-white border-white hover:bg-white hover:text-primary">
                  <Link to="/contact" className="flex items-center space-x-2 space-x-reverse">
                    <Phone className="w-6 h-6" />
                    <span>تماس فوری</span>
                  </Link>
                </Button>
              </div>
            </div>
            <div className="flex justify-center lg:justify-end animate-slide-in-right">
              <div className="relative">
                <img 
                  src={drImage} 
                  alt="دکتر جواد میربلوکی" 
                  className="w-80 h-80 lg:w-96 lg:h-96 rounded-full object-cover border-8 border-white shadow-2xl"
                />
                <div className="absolute -bottom-4 -right-4 bg-yellow-400 text-primary p-4 rounded-full">
                  <Award className="w-8 h-8" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-8">
            {stats.map((stat, index) => (
              <div key={index} className="text-center">
                <div className="text-3xl md:text-4xl font-bold text-primary mb-2 persian-numbers">
                  {stat.number}
                </div>
                <div className="text-muted-foreground">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Services Section */}
      <section className="py-20 bg-muted/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-foreground">خدمات تخصصی</h2>
            <p className="text-xl text-muted-foreground max-w-3xl mx-auto persian-text">
              ارائه خدمات جامع روانشناسی و مشاوره با جدیدترین روش‌های علمی و تجربه چندین ساله
            </p>
          </div>
          <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            {services.map((service, index) => (
              <Card key={index} className="text-center hover:shadow-lg transition-shadow">
                <CardHeader>
                  <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <service.icon className="w-8 h-8 text-primary" />
                  </div>
                  <CardTitle className="text-xl">{service.title}</CardTitle>
                </CardHeader>
                <CardContent>
                  <CardDescription className="persian-text">{service.description}</CardDescription>
                </CardContent>
              </Card>
            ))}
          </div>
          <div className="text-center mt-12">
            <Button size="lg" asChild>
              <Link to="/services">مشاهده تمام خدمات</Link>
            </Button>
          </div>
        </div>
      </section>

      {/* About Preview */}
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
              <h2 className="text-3xl md:text-4xl font-bold mb-6 text-foreground">درباره دکتر جواد میربلوکی</h2>
              <p className="text-lg text-muted-foreground mb-6 persian-text">
                کارشناس ارشد روانشناسی با بیش از ۱۰ سال تجربه در زمینه مشاوره خانواده و زوج‌درمانی. 
                دارای پروانه رسمی از سازمان نظام روانشناسی ایران و مدرس دوره‌های تخصصی روانشناسی.
              </p>
              <ul className="space-y-3 mb-8">
                <li className="flex items-center space-x-3 space-x-reverse">
                  <CheckCircle className="w-5 h-5 text-primary" />
                  <span>کارشناس ارشد روانشناسی</span>
                </li>
                <li className="flex items-center space-x-3 space-x-reverse">
                  <CheckCircle className="w-5 h-5 text-primary" />
                  <span>مشاور رسمی آموزش و پرورش</span>
                </li>
                <li className="flex items-center space-x-3 space-x-reverse">
                  <CheckCircle className="w-5 h-5 text-primary" />
                  <span>عضو انجمن روانشناسی ایران</span>
                </li>
                <li className="flex items-center space-x-3 space-x-reverse">
                  <CheckCircle className="w-5 h-5 text-primary" />
                  <span>بیش از ۱۰۰۰ مراجع موفق</span>
                </li>
              </ul>
              <Button size="lg" asChild>
                <Link to="/about">بیشتر بخوانید</Link>
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Testimonials */}
      <section className="py-20 bg-muted/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-foreground">نظرات مراجعین</h2>
            <p className="text-xl text-muted-foreground">تجربه موفق مراجعین ما</p>
          </div>
          <div className="grid md:grid-cols-3 gap-8">
            {testimonials.map((testimonial, index) => (
              <Card key={index} className="text-right">
                <CardHeader>
                  <div className="flex justify-end space-x-1 space-x-reverse mb-4">
                    {[...Array(testimonial.rating)].map((_, i) => (
                      <Star key={i} className="w-5 h-5 fill-yellow-400 text-yellow-400" />
                    ))}
                  </div>
                  <CardDescription className="text-lg persian-text">"{testimonial.text}"</CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="font-semibold">{testimonial.name}</p>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* Blog Preview */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-16">
            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-foreground">مقالات روانشناسی</h2>
            <p className="text-xl text-muted-foreground">آخرین مطالب آموزشی و تحلیلی</p>
          </div>
          <div className="grid md:grid-cols-3 gap-8">
            {blogPosts.map((post, index) => (
              <Card key={index} className="text-right hover:shadow-lg transition-shadow">
                <div className="aspect-video bg-cover bg-center rounded-t-lg" 
                     style={{ backgroundImage: `url(${post.image})` }} />
                <CardHeader>
                  <CardTitle className="text-xl">{post.title}</CardTitle>
                  <CardDescription className="persian-text">{post.excerpt}</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="flex justify-between items-center">
                    <Button variant="ghost" size="sm" className="p-0">
                      <Link to="/blog" className="flex items-center space-x-1 space-x-reverse">
                        <span>ادامه مطلب</span>
                        <ArrowLeft className="w-4 h-4" />
                      </Link>
                    </Button>
                    <span className="text-sm text-muted-foreground">{post.date}</span>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
          <div className="text-center mt-12">
            <Button size="lg" asChild>
              <Link to="/blog">مشاهده تمام مقالات</Link>
            </Button>
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 bg-gradient-hero text-white">
        <div className="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl md:text-4xl font-bold mb-6">آماده شروع مسیر بهبودی هستید؟</h2>
          <p className="text-xl mb-8 text-blue-100 persian-text">
            همین امروز جلسه مشاوره خود را رزرو کنید و قدم اول را برای زندگی بهتر بردارید
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
                <Phone className="w-6 h-6" />
                <span>تماس فوری</span>
              </Link>
            </Button>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  );
};

export default Index;
