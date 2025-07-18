import { useState } from "react";
import { Search, Calendar, User, ArrowLeft, Heart, Eye, MessageCircle } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import Navigation from "@/components/Navigation";
import Footer from "@/components/Footer";
import clinicImage from "@/assets/clinic-interior.jpg";

const Blog = () => {
  const [searchTerm, setSearchTerm] = useState("");

  const blogPosts = [
    {
      id: 1,
      title: "چگونه با استرس مقابله کنیم؟",
      excerpt: "استرس بخش جدایی‌ناپذیر زندگی مدرن است. در این مقاله روش‌های علمی و عملی برای مدیریت استرس را بررسی می‌کنیم.",
      content: "استرس در زندگی روزمره امری طبیعی است، اما زمانی که خارج از کنترل باشد می‌تواند تأثیرات منفی بر سلامت جسمی و روانی داشته باشد...",
      image: clinicImage,
      author: "دکتر جواد میربلوکی",
      date: "۱۵ دی ۱۴۰۳",
      readTime: "۵ دقیقه",
      views: 245,
      likes: 18,
      category: "مدیریت استرس",
      tags: ["استرس", "سلامت روان", "تکنیک‌های آرامش"]
    },
    {
      id: 2,
      title: "رازهای رابطه سالم",
      excerpt: "رابطه عاطفی سالم نیازمند تلاش دوطرفه و رعایت اصول خاصی است. در ادامه این اصول را بررسی می‌کنیم.",
      content: "رابطه عاطفی موفق بر پایه احترام متقابل، ارتباط مؤثر و تعهد مشترک بنا می‌شود...",
      image: clinicImage,
      author: "دکتر جواد میربلوکی",
      date: "۱۰ دی ۱۴۰۳",
      readTime: "۷ دقیقه",
      views: 189,
      likes: 24,
      category: "روابط",
      tags: ["رابطه", "زندگی زناشویی", "ارتباط"]
    },
    {
      id: 3,
      title: "نقش روانکاوی در درمان مشکلات عمیق",
      excerpt: "روانکاوی یکی از قدیمی‌ترین و مؤثرترین روش‌های درمان مشکلات روانی است که به بررسی عمیق ناخودآگاه می‌پردازد.",
      content: "روانکاوی با کمک به افراد برای شناخت الگوهای ناخودآگاه رفتاری، می‌تواند تغییرات عمیق و پایداری ایجاد کند...",
      image: clinicImage,
      author: "دکتر جواد میربلوکی",
      date: "۵ دی ۱۴۰۳",
      readTime: "۱۰ دقیقه",
      views: 156,
      likes: 31,
      category: "روانکاوی",
      tags: ["روانکاوی", "ناخودآگاه", "درمان"]
    },
    {
      id: 4,
      title: "نشانه‌های افسردگی در نوجوانان",
      excerpt: "تشخیص افسردگی در نوجوانان چالش‌برانگیز است زیرا علائم آن با تغییرات طبیعی دوران نوجوانی درهم آمیخته می‌شود.",
      content: "افسردگی نوجوانان موضوعی جدی است که نیاز به توجه والدین و متخصصان دارد...",
      image: clinicImage,
      author: "دکتر جواد میربلوکی",
      date: "۱ دی ۱۴۰۳",
      readTime: "۶ دقیقه",
      views: 203,
      likes: 15,
      category: "نوجوانان",
      tags: ["افسردگی", "نوجوان", "علائم"]
    },
    {
      id: 5,
      title: "تأثیر مدیتیشن بر سلامت روان",
      excerpt: "مدیتیشن به عنوان یک تمرین ذهنی، تأثیرات مثبت قابل توجهی بر سلامت روانی و کاهش اضطراب دارد.",
      content: "تحقیقات علمی نشان داده‌اند که مدیتیشن منظم می‌تواند ساختار مغز را تغییر دهد...",
      image: clinicImage,
      author: "دکتر جواد میربلوکی",
      date: "۲۸ آذر ۱۴۰۳",
      readTime: "۸ دقیقه",
      views: 178,
      likes: 22,
      category: "تکنیک‌های درمان",
      tags: ["مدیتیشن", "آرامش", "سلامت روان"]
    },
    {
      id: 6,
      title: "چگونه با فرزند نوجوان خود ارتباط برقرار کنیم؟",
      excerpt: "ارتباط با نوجوانان نیاز به درک ویژگی‌های این دوران و استفاده از تکنیک‌های مناسب دارد.",
      content: "دوران نوجوانی دوره‌ای پر از تغییرات جسمی، عاطفی و اجتماعی است...",
      image: clinicImage,
      author: "دکتر جواد میربلوکی",
      date: "۲۵ آذر ۱۴۰۳",
      readTime: "۹ دقیقه",
      views: 234,
      likes: 28,
      category: "تربیت فرزند",
      tags: ["نوجوان", "والدین", "ارتباط"]
    }
  ];

  const categories = [
    "همه",
    "مدیریت استرس",
    "روابط", 
    "روانکاوی",
    "نوجوانان",
    "تکنیک‌های درمان",
    "تربیت فرزند"
  ];

  const [selectedCategory, setSelectedCategory] = useState("همه");

  const filteredPosts = blogPosts.filter(post => {
    const matchesSearch = post.title.includes(searchTerm) || post.excerpt.includes(searchTerm);
    const matchesCategory = selectedCategory === "همه" || post.category === selectedCategory;
    return matchesSearch && matchesCategory;
  });

  const featuredPost = blogPosts[0];
  const recentPosts = blogPosts.slice(1, 4);

  return (
    <div className="min-h-screen bg-background">
      <Navigation />
      
      {/* Hero Section */}
      <section className="py-20 bg-gradient-hero text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h1 className="text-4xl md:text-5xl font-bold mb-6">بلاگ روانشناسی</h1>
            <p className="text-xl text-blue-100 max-w-3xl mx-auto persian-text">
              مقالات آموزشی و تحلیلی در زمینه روانشناسی، مشاوره و سلامت روان
            </p>
          </div>
        </div>
      </section>

      {/* Search and Filter */}
      <section className="py-12 bg-white border-b">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex flex-col md:flex-row gap-6 items-center justify-between">
            {/* Search */}
            <div className="relative flex-1 max-w-md">
              <Search className="absolute right-3 top-1/2 transform -translate-y-1/2 text-muted-foreground w-4 h-4" />
              <Input
                type="text"
                placeholder="جستجو در مقالات..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="pr-10 text-right"
              />
            </div>

            {/* Categories */}
            <div className="flex flex-wrap gap-2">
              {categories.map((category) => (
                <Button
                  key={category}
                  variant={selectedCategory === category ? "default" : "outline"}
                  size="sm"
                  onClick={() => setSelectedCategory(category)}
                  className="text-sm"
                >
                  {category}
                </Button>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Featured Post */}
      <section className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl font-bold mb-8 text-center">مقاله ویژه</h2>
          <Card className="overflow-hidden">
            <div className="grid lg:grid-cols-2 gap-0">
              <div className="aspect-video lg:aspect-auto">
                <img 
                  src={featuredPost.image} 
                  alt={featuredPost.title}
                  className="w-full h-full object-cover"
                />
              </div>
              <div className="p-8 text-right">
                <div className="flex items-center space-x-2 space-x-reverse mb-4">
                  <Badge variant="secondary">{featuredPost.category}</Badge>
                  <span className="text-sm text-muted-foreground">{featuredPost.readTime}</span>
                </div>
                <h3 className="text-2xl md:text-3xl font-bold mb-4">{featuredPost.title}</h3>
                <p className="text-muted-foreground mb-6 persian-text leading-relaxed">
                  {featuredPost.excerpt}
                </p>
                <div className="flex items-center justify-between mb-6">
                  <div className="flex items-center space-x-2 space-x-reverse text-sm text-muted-foreground">
                    <User className="w-4 h-4" />
                    <span>{featuredPost.author}</span>
                  </div>
                  <div className="flex items-center space-x-2 space-x-reverse text-sm text-muted-foreground">
                    <Calendar className="w-4 h-4" />
                    <span>{featuredPost.date}</span>
                  </div>
                </div>
                <div className="flex items-center justify-between">
                  <Button>
                    ادامه مطلب
                    <ArrowLeft className="w-4 h-4 mr-2" />
                  </Button>
                  <div className="flex items-center space-x-4 space-x-reverse text-sm text-muted-foreground">
                    <div className="flex items-center space-x-1 space-x-reverse">
                      <Eye className="w-4 h-4" />
                      <span>{featuredPost.views}</span>
                    </div>
                    <div className="flex items-center space-x-1 space-x-reverse">
                      <Heart className="w-4 h-4" />
                      <span>{featuredPost.likes}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </Card>
        </div>
      </section>

      {/* Blog Posts Grid */}
      <section className="py-16 bg-muted/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl font-bold mb-8 text-center">
            آخرین مقالات
            {selectedCategory !== "همه" && (
              <span className="text-primary"> - {selectedCategory}</span>
            )}
          </h2>
          
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {filteredPosts.map((post) => (
              <Card key={post.id} className="text-right hover:shadow-lg transition-shadow overflow-hidden">
                <div className="aspect-video bg-cover bg-center" 
                     style={{ backgroundImage: `url(${post.image})` }} />
                <CardHeader className="pb-4">
                  <div className="flex items-center justify-between mb-2">
                    <Badge variant="secondary" className="text-xs">{post.category}</Badge>
                    <span className="text-xs text-muted-foreground">{post.readTime}</span>
                  </div>
                  <CardTitle className="text-lg leading-relaxed">{post.title}</CardTitle>
                  <CardDescription className="persian-text line-clamp-3">{post.excerpt}</CardDescription>
                </CardHeader>
                <CardContent className="pt-0">
                  <div className="flex items-center justify-between text-sm text-muted-foreground mb-4">
                    <div className="flex items-center space-x-2 space-x-reverse">
                      <User className="w-3 h-3" />
                      <span>{post.author}</span>
                    </div>
                    <div className="flex items-center space-x-2 space-x-reverse">
                      <Calendar className="w-3 h-3" />
                      <span>{post.date}</span>
                    </div>
                  </div>
                  
                  <div className="flex items-center justify-between">
                    <Button variant="ghost" size="sm" className="p-0 h-auto">
                      ادامه مطلب
                      <ArrowLeft className="w-3 h-3 mr-1" />
                    </Button>
                    <div className="flex items-center space-x-3 space-x-reverse text-xs text-muted-foreground">
                      <div className="flex items-center space-x-1 space-x-reverse">
                        <Eye className="w-3 h-3" />
                        <span>{post.views}</span>
                      </div>
                      <div className="flex items-center space-x-1 space-x-reverse">
                        <Heart className="w-3 h-3" />
                        <span>{post.likes}</span>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>

          {filteredPosts.length === 0 && (
            <div className="text-center py-12">
              <MessageCircle className="w-16 h-16 text-muted-foreground mx-auto mb-4" />
              <h3 className="text-xl font-semibold mb-2">مقاله‌ای یافت نشد</h3>
              <p className="text-muted-foreground">
                متأسفانه مقاله‌ای با این عبارت جستجو یا دسته‌بندی یافت نشد.
              </p>
            </div>
          )}
        </div>
      </section>

      {/* Newsletter Signup */}
      <section className="py-16 bg-white">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 className="text-3xl font-bold mb-4">عضویت در خبرنامه</h2>
          <p className="text-lg text-muted-foreground mb-8 persian-text">
            برای دریافت آخرین مقالات روانشناسی و نکات مفید، در خبرنامه ما عضو شوید
          </p>
          <div className="flex flex-col sm:flex-row gap-4 max-w-md mx-auto">
            <Input 
              type="email" 
              placeholder="ایمیل خود را وارد کنید" 
              className="text-right flex-1"
              dir="ltr"
            />
            <Button>عضویت</Button>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  );
};

export default Blog;