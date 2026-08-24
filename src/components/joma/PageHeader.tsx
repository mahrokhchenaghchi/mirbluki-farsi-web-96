import { useNavigate } from "react-router-dom";
import { ChevronRight } from "lucide-react";
import { Button } from "@/components/ui/button";

export function PageHeader({
  title,
  description,
  crumbs,
  action,
}: {
  title: string;
  description?: string;
  crumbs?: Array<{ label: string; to?: string }>;
  action?: React.ReactNode;
}) {
  const navigate = useNavigate();
  return (
    <div className="space-y-3">
      <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
        <Button type="button" variant="ghost" size="sm" className="h-8 px-2" onClick={() => navigate(-1)}>
          <ChevronRight className="h-4 w-4" />
          بازگشت
        </Button>
        {crumbs?.map((crumb, index) => (
          <span key={`${crumb.label}-${index}`} className="flex items-center gap-2">
            <span>/</span>
            {crumb.to ? (
              <button type="button" className="hover:text-primary" onClick={() => navigate(crumb.to!)}>
                {crumb.label}
              </button>
            ) : (
              <span className="text-foreground">{crumb.label}</span>
            )}
          </span>
        ))}
      </div>
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-3xl font-black">{title}</h1>
          {description && <p className="mt-2 max-w-2xl text-sm leading-7 text-muted-foreground">{description}</p>}
        </div>
        {action}
      </div>
    </div>
  );
}
