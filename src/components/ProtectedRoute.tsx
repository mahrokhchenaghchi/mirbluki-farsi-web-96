import { useEffect } from "react";
import { useAuth } from "@/hooks/useAuth";
import { useNavigate, useLocation } from "react-router-dom";

interface ProtectedRouteProps {
  children: React.ReactNode;
  /** اگر true باشد فقط مدیران دسترسی دارند */
  adminOnly?: boolean;
}

const ProtectedRoute = ({ children, adminOnly = false }: ProtectedRouteProps) => {
  const { user, loading, isAdmin } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    if (loading) return;
    if (!user) {
      navigate(`/auth?next=${encodeURIComponent(location.pathname)}`, { replace: true });
    } else if (adminOnly && !isAdmin) {
      navigate("/", { replace: true });
    }
  }, [user, loading, isAdmin, adminOnly, navigate, location.pathname]);

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
      </div>
    );
  }

  if (!user || (adminOnly && !isAdmin)) return null;

  return <>{children}</>;
};

export default ProtectedRoute;
