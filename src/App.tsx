import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import { Toaster as Sonner } from "@/components/ui/sonner";
import { TooltipProvider } from "@/components/ui/tooltip";
import { AuthProvider } from "@/hooks/useAuth";
import { AppLayout } from "@/layouts/AppLayout";
import { ProtectedLayout } from "@/layouts/ProtectedLayout";
import { appBasename } from "@/lib/base";
import AboutPage from "@/pages/About";
import AuthPage from "@/pages/Auth";
import Dashboard from "@/pages/Dashboard";
import Landing from "@/pages/Landing";
import LibraryPage from "@/pages/Library";
import MoodPage from "@/pages/Mood";
import NotFound from "@/pages/NotFound";
import PeriodDetailPage from "@/pages/PeriodDetail";
import PeriodsPage from "@/pages/Periods";
import PlanPage from "@/pages/Plan";
import ProfilePage from "@/pages/Profile";
import ReportsPage from "@/pages/Reports";
import SettingsPage from "@/pages/Settings";
import SupportPage from "@/pages/Support";
import TodayPage from "@/pages/Today";

const queryClient = new QueryClient();

const App = () => (
  <QueryClientProvider client={queryClient}>
    <TooltipProvider>
      <Sonner />
      <BrowserRouter basename={appBasename() || undefined}>
        <AuthProvider>
          <Routes>
            <Route path="/" element={<Landing />} />
            <Route path="/auth" element={<AuthPage />} />
            <Route path="/about" element={<AboutPage />} />
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
                <Route path="mood" element={<MoodPage />} />
                <Route path="profile" element={<ProfilePage />} />
                <Route path="settings" element={<SettingsPage />} />
                <Route path="about" element={<AboutPage />} />
                <Route path="support" element={<SupportPage />} />
              </Route>
            </Route>
            <Route path="*" element={<NotFound />} />
          </Routes>
        </AuthProvider>
      </BrowserRouter>
    </TooltipProvider>
  </QueryClientProvider>
);

export default App;
