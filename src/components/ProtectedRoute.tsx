import { useEffect } from 'react';
import { useAuth } from '@/hooks/useAuth';
import { useNavigate, useLocation } from 'react-router-dom';

interface ProtectedRouteProps {
  children: React.ReactNode;
}

const ProtectedRoute = ({ children }: ProtectedRouteProps) => {
  const { user, loading } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    if (!loading) {
      if (!user && location.pathname !== '/auth') {
        navigate('/auth');
      } else if (user && location.pathname === '/auth') {
        navigate('/appointments');
      }
    }
  }, [user, loading, navigate, location.pathname]);

  // Show loading state while checking authentication
  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-primary"></div>
      </div>
    );
  }

  // Allow access to auth page when not logged in
  if (!user && location.pathname === '/auth') {
    return <>{children}</>;
  }

  // Require authentication for all other pages
  if (!user) {
    return null; // This will trigger the redirect in useEffect
  }

  return <>{children}</>;
};

export default ProtectedRoute;