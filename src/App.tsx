import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { AuthProvider } from "@/hooks/useAuth";
import { AppLayout } from "@/layouts/AppLayout";
import { ProtectedLayout } from "@/layouts/ProtectedLayout";
import Landing from "@/pages/Landing";
import AuthPage from "@/pages/Auth";
import MoodPage from "@/pages/Mood";
import Dashboard from "@/pages/Dashboard";
import TodayPage from "@/pages/Today";
import PlanPage from "@/pages/Plan";
import LibraryPage from "@/pages/Library";
import ReportsPage from "@/pages/Reports";
import PeriodsPage from "@/pages/Periods";
import PeriodDetailPage from "@/pages/PeriodDetail";
import AboutPage from "@/pages/About";
import SupportPage from "@/pages/Support";
import NotFound from "@/pages/NotFound";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Sonner />
      <BrowserRouter>
        <AuthProvider>
          <Routes>
            <Route path="/" element={<Landing />} />
            <Route path="/auth" element={<AuthPage />} />
            <Route element={<ProtectedLayout />}>
              <Route path="/mood" element={<MoodPage />} />
              <Route path="/app" element={<AppLayout />}>
                <Route index element={<Dashboard />} />
                <Route path="today" element={<TodayPage />} />
                <Route path="plan" element={<PlanPage />} />
                <Route path="library" element={<LibraryPage />} />
                <Route path="reports" element={<ReportsPage />} />
                <Route path="periods" element={<PeriodsPage />} />
                <Route path="periods/:periodKey" element={<PeriodDetailPage />} />
                <Route path="about" element={<AboutPage />} />
                <Route path="support" element={<SupportPage />} />
              </Route>
            </Route>
            <Route path="/about" element={<AboutPage />} />
            <Route path="*" element={<NotFound />} />
          </Routes>
        </AuthProvider>
      </BrowserRouter>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
