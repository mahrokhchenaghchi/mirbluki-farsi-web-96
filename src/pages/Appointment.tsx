import { useState } from "react";
import { Calendar, Clock, User, Phone, MessageCircle, CheckCircle, AlertCircle } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group";
import Navigation from "@/components/Navigation";
import Footer from "@/components/Footer";
import { useToast } from "@/hooks/use-toast";

const Appointment = () => {
  const [formData, setFormData] = useState({
    firstName: "",
    lastName: "",
    phone: "",
    email: "",
    serviceType: "",
    sessionType: "",
    preferredTime: "",
    preferredDate: "",
    message: ""
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { toast } = useToast();

  const services = [
    { value: "couple-therapy", label: "زوج‌درمانی" },
    { value: "psychoanalysis", label: "روانکاوی" },
    { value: "family-counseling", label: "مشاوره خانواده" },
    { value: "depression-anxiety", label: "درمان افسردگی و اضطراب" },
    { value: "teen-counseling", label: "مشاوره نوجوان" },
    { value: "sex-therapy", label: "سکس‌تراپی" },
    { value: "psychological-tests", label: "تست‌های روانشناختی" },
    { value: "consultation", label: "مشاوره کلی" }
  ];

  const timeSlots = [
    "۹:۰۰ - ۱۰:۰۰",
    "۱۰:۰۰ - ۱۱:۰۰", 
    "۱۱:۰۰ - ۱۲:۰۰",
    "۱۴:۰۰ - ۱۵:۰۰",
    "۱۵:۰۰ - ۱۶:۰۰",
    "۱۶:۰۰ - ۱۷:۰۰",
    "۱۷:۰۰ - ۱۸:۰۰",
    "۱۸:۰۰ - ۱۹:۰۰",
    "۱۹:۰۰ - ۲۰:۰۰"
  ];

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    // Simulate form submission
    await new Promise(resolve => setTimeout(resolve, 2000));

    toast({
      title: "درخواست شما ثبت شد",
      description: "در کمتر از ۲۴ ساعت با شما تماس خواهیم گرفت",
    });

    setIsSubmitting(false);
    // Reset form
    setFormData({
      firstName: "",
      lastName: "",
      phone: "",
      email: "",
      serviceType: "",
      sessionType: "",
      preferredTime: "",
      preferredDate: "",
      message: ""
    });
  };

  return (
    <div className="min-h-screen bg-background">
      <Navigation />
      
      {/* Hero Section */}
      <section className="py-20 bg-gradient-hero text-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center">
            <h1 className="text-4xl md:text-5xl font-bold mb-6">رزرو وقت مشاوره</h1>
            <p className="text-xl text-blue-100 max-w-3xl mx-auto persian-text">
              فرم زیر را تکمیل کنید تا بتوانیم بهترین زمان را برای جلسه شما در نظر بگیریم
            </p>
          </div>
        </div>
      </section>

      {/* Appointment Form */}
      <section className="py-20 bg-white">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <form onSubmit={handleSubmit} className="space-y-8">
            {/* Personal Information */}
            <Card>
              <CardHeader>
                <CardTitle className="text-right flex items-center space-x-2 space-x-reverse">
                  <User className="w-6 h-6" />
                  <span>اطلاعات شخصی</span>
                </CardTitle>
                <CardDescription className="text-right">
                  اطلاعات تماس خود را وارد کنید
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="firstName" className="text-right">نام *</Label>
                    <Input 
                      id="firstName" 
                      value={formData.firstName}
                      onChange={(e) => setFormData({...formData, firstName: e.target.value})}
                      placeholder="نام خود را وارد کنید" 
                      className="text-right"
                      required 
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="lastName" className="text-right">نام خانوادگی *</Label>
                    <Input 
                      id="lastName" 
                      value={formData.lastName}
                      onChange={(e) => setFormData({...formData, lastName: e.target.value})}
                      placeholder="نام خانوادگی خود را وارد کنید" 
                      className="text-right"
                      required 
                    />
                  </div>
                </div>
                
                <div className="space-y-2">
                  <Label htmlFor="phone" className="text-right">شماره تماس *</Label>
                  <Input 
                    id="phone" 
                    type="tel" 
                    value={formData.phone}
                    onChange={(e) => setFormData({...formData, phone: e.target.value})}
                    placeholder="۰۹۱۲-۳۴۵۶۷۸۹" 
                    className="text-right" 
                    dir="ltr"
                    required 
                  />
                </div>
                
                <div className="space-y-2">
                  <Label htmlFor="email" className="text-right">ایمیل (اختیاری)</Label>
                  <Input 
                    id="email" 
                    type="email" 
                    value={formData.email}
                    onChange={(e) => setFormData({...formData, email: e.target.value})}
                    placeholder="example@email.com" 
                    className="text-right" 
                    dir="ltr" 
                  />
                </div>
              </CardContent>
            </Card>

            {/* Service Selection */}
            <Card>
              <CardHeader>
                <CardTitle className="text-right flex items-center space-x-2 space-x-reverse">
                  <MessageCircle className="w-6 h-6" />
                  <span>نوع خدمات</span>
                </CardTitle>
                <CardDescription className="text-right">
                  نوع مشاوره مورد نیاز خود را انتخاب کنید
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label className="text-right">نوع مشاوره *</Label>
                  <Select value={formData.serviceType} onValueChange={(value) => setFormData({...formData, serviceType: value})} required>
                    <SelectTrigger className="text-right">
                      <SelectValue placeholder="نوع مشاوره را انتخاب کنید" />
                    </SelectTrigger>
                    <SelectContent>
                      {services.map((service) => (
                        <SelectItem key={service.value} value={service.value}>
                          {service.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                <div className="space-y-2">
                  <Label className="text-right">نحوه برگزاری جلسه *</Label>
                  <RadioGroup 
                    value={formData.sessionType} 
                    onValueChange={(value) => setFormData({...formData, sessionType: value})}
                    className="flex flex-col space-y-2"
                  >
                    <div className="flex items-center space-x-2 space-x-reverse">
                      <RadioGroupItem value="in-person" id="in-person" />
                      <Label htmlFor="in-person" className="text-right cursor-pointer">
                        حضوری (در کلینیک)
                      </Label>
                    </div>
                    <div className="flex items-center space-x-2 space-x-reverse">
                      <RadioGroupItem value="online" id="online" />
                      <Label htmlFor="online" className="text-right cursor-pointer">
                        آنلاین (ویدیوکال)
                      </Label>
                    </div>
                  </RadioGroup>
                </div>
              </CardContent>
            </Card>

            {/* Date and Time */}
            <Card>
              <CardHeader>
                <CardTitle className="text-right flex items-center space-x-2 space-x-reverse">
                  <Calendar className="w-6 h-6" />
                  <span>زمان‌بندی</span>
                </CardTitle>
                <CardDescription className="text-right">
                  تاریخ و زمان ترجیحی خود را انتخاب کنید
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label htmlFor="preferredDate" className="text-right">تاریخ ترجیحی *</Label>
                    <Input 
                      id="preferredDate" 
                      type="date" 
                      value={formData.preferredDate}
                      onChange={(e) => setFormData({...formData, preferredDate: e.target.value})}
                      className="text-right"
                      required 
                    />
                  </div>
                  <div className="space-y-2">
                    <Label className="text-right">زمان ترجیحی *</Label>
                    <Select value={formData.preferredTime} onValueChange={(value) => setFormData({...formData, preferredTime: value})} required>
                      <SelectTrigger className="text-right">
                        <SelectValue placeholder="زمان مورد نظر را انتخاب کنید" />
                      </SelectTrigger>
                      <SelectContent>
                        {timeSlots.map((time) => (
                          <SelectItem key={time} value={time}>
                            {time}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Additional Information */}
            <Card>
              <CardHeader>
                <CardTitle className="text-right">توضیحات اضافی</CardTitle>
                <CardDescription className="text-right">
                  در صورت تمایل، اطلاعات بیشتری در مورد موضوع مشاوره ارائه دهید
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  <Label htmlFor="message" className="text-right">پیام (اختیاری)</Label>
                  <Textarea 
                    id="message" 
                    value={formData.message}
                    onChange={(e) => setFormData({...formData, message: e.target.value})}
                    placeholder="اگر توضیح خاصی در مورد مشکل یا درخواست خود دارید، در اینجا بنویسید..." 
                    className="text-right h-32"
                    rows={5}
                  />
                </div>
              </CardContent>
            </Card>

            {/* Important Notes */}
            <Card className="border-orange-200 bg-orange-50">
              <CardContent className="p-6">
                <div className="flex items-start space-x-3 space-x-reverse">
                  <AlertCircle className="w-6 h-6 text-orange-600 flex-shrink-0 mt-0.5" />
                  <div className="text-right">
                    <h3 className="font-semibold text-orange-800 mb-2">نکات مهم:</h3>
                    <ul className="space-y-2 text-orange-700 text-sm">
                      <li>• پس از ثبت درخواست، در کمتر از ۲۴ ساعت با شما تماس خواهیم گرفت</li>
                      <li>• لطفاً حداقل ۲۴ ساعت قبل از جلسه، زمان نهایی را تأیید کنید</li>
                      <li>• در صورت نیاز به تغییر وقت، حداقل ۱۲ ساعت قبل اطلاع دهید</li>
                      <li>• برای جلسات آنلاین، لینک ویدیوکال قبل از جلسه ارسال خواهد شد</li>
                    </ul>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Submit Button */}
            <div className="text-center">
              <Button 
                type="submit" 
                size="lg" 
                className="px-8 py-4 text-lg"
                disabled={isSubmitting}
              >
                {isSubmitting ? (
                  <>
                    <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white ml-2"></div>
                    در حال ثبت...
                  </>
                ) : (
                  <>
                    <CheckCircle className="w-6 h-6 ml-2" />
                    ثبت درخواست
                  </>
                )}
              </Button>
            </div>
          </form>
        </div>
      </section>

      {/* Contact Info */}
      <section className="py-20 bg-muted/30">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
          <h2 className="text-3xl font-bold mb-6">نیاز به کمک فوری دارید؟</h2>
          <p className="text-lg text-muted-foreground mb-8 persian-text">
            برای مشاوره فوری یا سوالات تکمیلی، می‌توانید مستقیماً با ما تماس بگیرید
          </p>
          <div className="flex flex-col sm:flex-row gap-4 justify-center">
            <Button size="lg" variant="outline" className="flex items-center space-x-2 space-x-reverse">
              <Phone className="w-5 h-5" />
              <span>۰۲۶-۳۴۵۶۷۸۹۰</span>
            </Button>
            <Button size="lg" className="flex items-center space-x-2 space-x-reverse">
              <MessageCircle className="w-5 h-5" />
              <span>واتساپ</span>
            </Button>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  );
};

export default Appointment;