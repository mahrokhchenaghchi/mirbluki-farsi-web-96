import { useState } from "react";
import { Link, useLocation } from "react-router-dom";
import { Menu, X, Phone, Calendar } from "lucide-react";
import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";

const Navigation = () => {
  const [isOpen, setIsOpen] = useState(false);
  const location = useLocation();

  const navItems = [
    { name: "صفحه اصلی", href: "/" },
    { name: "درباره من", href: "/about" },
    { name: "خدمات", href: "/services" },
    { name: "بلاگ", href: "/blog" },
    { name: "تماس", href: "/contact" },
    { name: "کارت ویزیت", href: "/card" },
  ];

  const isActive = (path: string) => location.pathname === path;

  return (
    <nav className="bg-white/95 backdrop-blur-sm border-b border-border sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          {/* Logo/Brand */}
          <Link to="/" className="flex items-center space-x-2 space-x-reverse">
            <div className="w-10 h-10 bg-gradient-hero rounded-full flex items-center justify-center">
              <span className="text-white font-bold text-lg">ج.م</span>
            </div>
            <div className="text-right">
              <h1 className="text-lg font-bold text-foreground">جواد میربلوکی</h1>
              <p className="text-sm text-muted-foreground">روانشناس و مشاور</p>
            </div>
          </Link>

          {/* Desktop Navigation */}
          <div className="hidden md:flex items-center space-x-8 space-x-reverse">
            {navItems.map((item) => (
              <Link
                key={item.name}
                to={item.href}
                className={cn(
                  "px-3 py-2 text-sm font-medium transition-colors relative",
                  isActive(item.href)
                    ? "text-primary"
                    : "text-foreground hover:text-primary"
                )}
              >
                {item.name}
                {isActive(item.href) && (
                  <div className="absolute bottom-0 left-0 right-0 h-0.5 bg-primary" />
                )}
              </Link>
            ))}
          </div>

          {/* CTA Buttons */}
          <div className="hidden md:flex items-center space-x-4 space-x-reverse">
            <Button variant="outline" size="sm" asChild>
              <Link to="/contact" className="flex items-center space-x-2 space-x-reverse">
                <Phone className="w-4 h-4" />
                <span>تماس</span>
              </Link>
            </Button>
            <Button size="sm" asChild>
              <Link to="/appointment" className="flex items-center space-x-2 space-x-reverse">
                <Calendar className="w-4 h-4" />
                <span>رزرو وقت</span>
              </Link>
            </Button>
          </div>

          {/* Mobile menu button */}
          <div className="md:hidden">
            <Button
              variant="ghost"
              size="icon"
              onClick={() => setIsOpen(!isOpen)}
            >
              {isOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </Button>
          </div>
        </div>

        {/* Mobile Navigation */}
        {isOpen && (
          <div className="md:hidden border-t border-border bg-white">
            <div className="px-2 pt-2 pb-3 space-y-1">
              {navItems.map((item) => (
                <Link
                  key={item.name}
                  to={item.href}
                  className={cn(
                    "block px-3 py-2 text-base font-medium rounded-md transition-colors",
                    isActive(item.href)
                      ? "text-primary bg-primary/10"
                      : "text-foreground hover:text-primary hover:bg-muted"
                  )}
                  onClick={() => setIsOpen(false)}
                >
                  {item.name}
                </Link>
              ))}
              <div className="pt-4 space-y-2">
                <Button variant="outline" size="sm" asChild className="w-full">
                  <Link to="/contact" className="flex items-center space-x-2 space-x-reverse">
                    <Phone className="w-4 h-4" />
                    <span>تماس</span>
                  </Link>
                </Button>
                <Button size="sm" asChild className="w-full">
                  <Link to="/appointment" className="flex items-center space-x-2 space-x-reverse">
                    <Calendar className="w-4 h-4" />
                    <span>رزرو وقت</span>
                  </Link>
                </Button>
              </div>
            </div>
          </div>
        )}
      </div>
    </nav>
  );
};

export default Navigation;