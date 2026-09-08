import { lazy, Suspense } from "react";
import { Toaster } from "@/components/ui/toaster";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, HashRouter, Routes, Route, Navigate } from "react-router-dom";
import { AuthProvider } from "@/hooks/useAuth";
import ProtectedRoute from "@/components/ProtectedRoute";
import { CartProvider } from "@/store/CartContext";

import SiteLayout from "@/layouts/SiteLayout";
import Home from "@/pages/Home";
import Menu from "@/pages/Menu";
import Branches from "@/pages/Branches";
import About from "@/pages/About";
import Contact from "@/pages/Contact";
import Auth from "@/pages/Auth";
import Checkout from "@/pages/Checkout";
import Track from "@/pages/Track";
import Account from "@/pages/Account";
import NotFound from "@/pages/NotFound";

/* پنل مدیریت به صورت تنبل (lazy) بارگذاری می‌شود تا جاوااسکریپت
   صفحات مشتری سبک بماند — نمودارها و جدول‌های ادمین فقط وقت نیاز دانلود می‌شوند */
const AdminLayout = lazy(() => import("@/layouts/AdminLayout"));
const AdminDashboard = lazy(() => import("@/pages/admin/Dashboard"));
const AdminOrders = lazy(() => import("@/pages/admin/Orders"));
const AdminMenu = lazy(() => import("@/pages/admin/Menu"));
const AdminBranches = lazy(() => import("@/pages/admin/Branches"));
const AdminCoupons = lazy(() => import("@/pages/admin/Coupons"));
const AdminCustomers = lazy(() => import("@/pages/admin/Customers"));
const AdminSettings = lazy(() => import("@/pages/admin/Settings"));

const AdminFallback = () => (
  <div className="flex min-h-dvh items-center justify-center bg-[hsl(30_15%_95%)]">
    <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-primary" />
  </div>
);

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      staleTime: 30_000,
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

/**
 * انتخاب روتر بر اساس محیط اجرا:
 *  - بیلد دموی تک‌فایلی (__DEMO_BUILD__) یا باز شدن مستقیم فایل (file://)
 *    → HashRouter تا روی هر آدرسی کار کند (دابل‌کلیک، CDN، زیرمسیر سایت)
 *  - محیط توسعه/استقرار عادی → BrowserRouter با آدرس‌های تمیز
 */
const isStandaloneDemo =
  (typeof __DEMO_BUILD__ !== "undefined" && __DEMO_BUILD__) ||
  (typeof window !== "undefined" && window.location.protocol === "file:");

const Router = isStandaloneDemo ? HashRouter : BrowserRouter;

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Toaster />
      <Sonner position="top-center" dir="rtl" />
      <Router>
        <AuthProvider>
          <CartProvider>
            <Routes>
              {/* صفحات عمومی سایت */}
              <Route element={<SiteLayout />}>
                <Route path="/" element={<Home />} />
                <Route path="/menu" element={<Menu />} />
                <Route path="/branches" element={<Branches />} />
                <Route path="/about" element={<About />} />
                <Route path="/contact" element={<Contact />} />
                <Route path="/auth" element={<Auth />} />
                <Route path="/track" element={<Track />} />
                <Route
                  path="/checkout"
                  element={
                    <ProtectedRoute>
                      <Checkout />
                    </ProtectedRoute>
                  }
                />
                <Route
                  path="/account"
                  element={
                    <ProtectedRoute>
                      <Account />
                    </ProtectedRoute>
                  }
                />
                <Route path="*" element={<NotFound />} />
              </Route>

              {/* پنل مدیریت — فقط مدیر */}
              <Route
                path="/admin"
                element={
                  <Suspense fallback={<AdminFallback />}>
                    <ProtectedRoute adminOnly>
                      <AdminLayout />
                    </ProtectedRoute>
                  </Suspense>
                }
              >
                <Route index element={<AdminDashboard />} />
                <Route path="orders" element={<AdminOrders />} />
                <Route path="menu" element={<AdminMenu />} />
                <Route path="branches" element={<AdminBranches />} />
                <Route path="coupons" element={<AdminCoupons />} />
                <Route path="customers" element={<AdminCustomers />} />
                <Route path="settings" element={<AdminSettings />} />
                <Route path="*" element={<Navigate to="/admin" replace />} />
              </Route>
            </Routes>
          </CartProvider>
        </AuthProvider>
      </Router>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
