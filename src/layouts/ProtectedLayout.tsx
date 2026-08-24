import { Navigate, Outlet, useLocation } from "react-router-dom";
import { useAuth } from "@/hooks/useAuth";
import { LoadingState } from "@/components/joma/LoadingState";

export function ProtectedLayout() {
  const { user, loading, configured } = useAuth();
  const location = useLocation();

  if (!configured) {
    return <Navigate to="/" replace />;
  }

  if (loading) {
    return <LoadingState label="در حال بررسی نشست..." />;
  }

  if (!user) {
    return <Navigate to="/auth" replace state={{ from: location.pathname }} />;
  }

  return <Outlet />;
}
