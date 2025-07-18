import { Phone, Mail, MapPin, Clock, MessageCircle, Instagram, Calendar } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import Navigation from "@/components/Navigation";
import Footer from "@/components/Footer";
import { Link } from "react-router-dom";

const Contact = () => {
  return (
    <div className="min-h-screen bg-background">
      <Navigation />
      
      {/* Hero Section */}
      <section className="py-20 bg-gradient-hero text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h1 className="text-4xl md:text-5xl font-bold mb-6">تماس با ما</h1>
            <p className="text-xl text-blue-100 max-w-3xl mx-auto persian-text">
              برای دریافت مشاوره یا رزرو وقت با ما در تماس باشید
            </p>
          </div>
        </div>
      </section>

      {/* Contact Information & Form */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="grid lg:grid-cols-2 gap-12">
            {/* Contact Information */}
            <div className="space-y-8">
              <div>
                <h2 className="text-3xl font-bold mb-6 text-foreground">اطلاعات تماس</h2>
                <p className="text-lg text-muted-foreground mb-8 persian-text">
                  می‌توانید از طریق راه‌های زیر با ما در ارتباط باشید. ما آماده پاسخگویی به سوالات شما هستیم.
                </p>
              </div>

              <div className="space-y-6">
                <Card>
                  <CardContent className="p-6">
                    <div className="flex items-center space-x-4 space-x-reverse">
                      <div className="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <MapPin className="w-6 h-6 text-primary" />
                      </div>
                      <div className="text-right">
                        <h3 className="font-semibold text-lg">آدرس کلینیک</h3>
                        <p className="text-muted-foreground persian-text">
                          کرج، چهارراه هفت تیر، کوچه انقلاب، برج ارم، طبقه چهارم، واحد ۴۰۱
                        </p>
                      </div>
                    </div>
                  </CardContent>
                </Card>

                <Card>
                  <CardContent className="p-6">
                    <div className="flex items-center space-x-4 space-x-reverse">
                      <div className="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <Phone className="w-6 h-6 text-primary" />
                      </div>
                      <div className="text-right">
                        <h3 className="font-semibold text-lg">تلفن تماس</h3>
                        <p className="text-muted-foreground" dir="ltr">026-34567890</p>
                        <p className="text-sm text-muted-foreground">شنبه تا چهارشنبه، ۹ صبح تا ۸ شب</p>
                      </div>
                    </div>
                  </CardContent>
                </Card>

                <Card>
                  <CardContent className="p-6">
                    <div className="flex items-center space-x-4 space-x-reverse">
                      <div className="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <MessageCircle className="w-6 h-6 text-primary" />
                      </div>
                      <div className="text-right">
                        <h3 className="font-semibold text-lg">واتساپ و تلگرام</h3>
                        <p className="text-muted-foreground" dir="ltr">0912-3456789</p>
                        <p className="text-sm text-muted-foreground">پاسخگویی ۲۴ ساعته</p>
                      </div>
                    </div>
                  </CardContent>
                </Card>

                <Card>
                  <CardContent className="p-6">
                    <div className="flex items-center space-x-4 space-x-reverse">
                      <div className="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                        <Mail className="w-6 h-6 text-primary" />
                      </div>
                      <div className="text-right">
                        <h3 className="font-semibold text-lg">ایمیل</h3>
                        <p className="text-muted-foreground" dir="ltr">info@javadmirbluki.com</p>
                        <p className="text-sm text-muted-foreground">پاسخ در کمتر از ۲۴ ساعت</p>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </div>

              {/* Social Media */}
              <div>
                <h3 className="text-xl font-semibold mb-4">شبکه‌های اجتماعی</h3>
                <div className="flex space-x-4 space-x-reverse">
                  <a href="#" className="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                    <Instagram className="w-6 h-6" />
                  </a>
                  <a href="#" className="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                    <MessageCircle className="w-6 h-6" />
                  </a>
                </div>
              </div>
            </div>

            {/* Contact Form */}
            <div>
              <Card>
                <CardHeader>
                  <CardTitle className="text-2xl text-right">ارسال پیام</CardTitle>
                  <CardDescription className="text-right persian-text">
                    پیام خود را برای ما ارسال کنید و در اسرع وقت پاسخ شما را خواهیم داد
                  </CardDescription>
                </CardHeader>
                <CardContent className="space-y-4">
                  <form className="space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                      <div className="space-y-2">
                        <Label htmlFor="firstName" className="text-right">نام</Label>
                        <Input id="firstName" placeholder="نام خود را وارد کنید" className="text-right" />
                      </div>
                      <div className="space-y-2">
                        <Label htmlFor="lastName" className="text-right">نام خانوادگی</Label>
                        <Input id="lastName" placeholder="نام خانوادگی خود را وارد کنید" className="text-right" />
                      </div>
                    </div>
                    
                    <div className="space-y-2">
                      <Label htmlFor="phone" className="text-right">شماره تماس</Label>
                      <Input id="phone" type="tel" placeholder="شماره تماس خود را وارد کنید" className="text-right" dir="ltr" />
                    </div>
                    
                    <div className="space-y-2">
                      <Label htmlFor="email" className="text-right">ایمیل (اختیاری)</Label>
                      <Input id="email" type="email" placeholder="ایمیل خود را وارد کنید" className="text-right" dir="ltr" />
                    </div>
                    
                    <div className="space-y-2">
                      <Label htmlFor="subject" className="text-right">موضوع</Label>
                      <Input id="subject" placeholder="موضوع پیام خود را وارد کنید" className="text-right" />
                    </div>
                    
                    <div className="space-y-2">
                      <Label htmlFor="message" className="text-right">پیام</Label>
                      <Textarea 
                        id="message" 
                        placeholder="پیام خود را در اینجا بنویسید..." 
                        className="text-right h-32"
                        rows={5}
                      />
                    </div>
                    
                    <Button type="submit" className="w-full">ارسال پیام</Button>
                  </form>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </section>

      {/* Working Hours */}
      <section className="py-20 bg-muted/30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-foreground">ساعات کاری</h2>
            <p className="text-xl text-muted-foreground">برنامه زمانی ارائه خدمات</p>
          </div>
          
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-4xl mx-auto">
            {[
              { day: "شنبه تا چهارشنبه", hours: "۹:۰۰ - ۲۰:۰۰", type: "حضوری و آنلاین" },
              { day: "پنج‌شنبه", hours: "۹:۰۰ - ۱۴:۰۰", type: "فقط آنلاین" },
              { day: "جمعه", hours: "تعطیل", type: "اورژانس تلفنی" }
            ].map((schedule, index) => (
              <Card key={index} className="text-center">
                <CardContent className="p-6">
                  <div className="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Clock className="w-6 h-6 text-primary" />
                  </div>
                  <h3 className="font-semibold text-lg mb-2">{schedule.day}</h3>
                  <p className="text-2xl font-bold text-primary mb-1">{schedule.hours}</p>
                  <p className="text-sm text-muted-foreground">{schedule.type}</p>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* Map Section */}
      <section className="py-20 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center mb-12">
            <h2 className="text-3xl md:text-4xl font-bold mb-4 text-foreground">موقعیت کلینیک</h2>
            <p className="text-xl text-muted-foreground">آدرس دقیق کلینیک روانشناسی</p>
          </div>
          
          <div className="bg-muted/30 rounded-lg p-8 text-center">
            <MapPin className="w-16 h-16 text-primary mx-auto mb-4" />
            <h3 className="text-2xl font-bold mb-4">برج ارم، کرج</h3>
            <p className="text-lg text-muted-foreground mb-6 persian-text">
              کرج، چهارراه هفت تیر، کوچه انقلاب، برج ارم، طبقه چهارم، واحد ۴۰۱
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button variant="outline" size="lg">
                مشاهده در نقشه
              </Button>
              <Button size="lg" asChild>
                <Link to="/appointment">رزرو وقت</Link>
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Emergency Contact */}
      <section className="py-20 bg-gradient-hero text-white">
        <div className="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
          <h2 className="text-3xl md:text-4xl font-bold mb-6">تماس اضطراری</h2>
          <p className="text-xl mb-8 text-blue-100 persian-text">
            در مواقع اضطراری و خارج از ساعات کاری، می‌توانید از طریق واتساپ با ما در تماس باشید
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button size="lg" variant="secondary" className="text-lg px-8 py-4">
              <MessageCircle className="w-6 h-6 ml-2" />
              واتساپ اضطراری
            </Button>
            <Button size="lg" variant="outline" className="text-lg px-8 py-4 text-white border-white hover:bg-white hover:text-primary">
              <Phone className="w-6 h-6 ml-2" />
              تماس فوری
            </Button>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  );
};

export default Contact;