import { useNavigate } from "react-router-dom";
import { MapPin, Phone, Clock, Bike, Navigation, CheckCircle2 } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import { Skeleton } from "@/components/ui/skeleton";
import { useBranches } from "@/hooks/useData";
import { useCart } from "@/store/CartContext";
import { faDigits, isBranchOpen, toman } from "@/lib/format";
import { cn } from "@/lib/utils";

export default function Branches() {
  const navigate = useNavigate();
  const { data: branches, isLoading } = useBranches();
  const { branchId, setBranch } = useCart();

  if (isLoading) {
    return (
      <div className="container grid gap-6 py-12 md:grid-cols-2">
        {Array.from({ length: 4 }).map((_, i) => (
          <Skeleton key={i} className="h-64 rounded-3xl" />
        ))}
      </div>
    );
  }

  return (
    <div className="container py-12">
      <div className="mx-auto max-w-xl text-center">
        <p className="text-xs font-bold uppercase tracking-wider text-primary">شعب ما</p>
        <h1 className="mt-2 text-3xl font-black">نزدیک‌ترین شعبه را پیدا کنید</h1>
        <p className="persian-text mt-3 text-sm text-muted-foreground">
          هر شعبه منوی کامل، پیک اختصاصی و ساعت کاری خودش را دارد. شعبه موردنظر را انتخاب کنید
          تا منوی همان شعبه با هزینه ارسال دقیق نمایش داده شود.
        </p>
      </div>

      <div className="mt-10 grid gap-6 md:grid-cols-2">
        {(branches ?? []).map((b) => {
          const open = isBranchOpen(b.open_time, b.close_time);
          const selected = b.id === branchId;
          return (
            <Card
              key={b.id}
              className={cn(
                "card-hover overflow-hidden",
                selected && "ring-2 ring-primary ring-offset-2 ring-offset-background"
              )}
            >
              <div className="relative h-48">
                <img
                  src={b.image ?? "/images/brand/branch-default.jpg"}
                  alt={b.name}
                  className="h-full w-full object-cover"
                  loading="lazy"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                <div className="absolute bottom-3 right-4 text-white">
                  <h2 className="text-lg font-extrabold">{b.name}</h2>
                  <p className="text-xs opacity-80">{b.city}</p>
                </div>
                <Badge className={`absolute left-3 top-3 ${open ? "bg-emerald-600" : "bg-rose-600"} text-white`}>
                  {open ? "باز است" : "بسته"}
                </Badge>
                {selected && (
                  <Badge className="absolute right-3 top-3 bg-primary text-white">
                    <CheckCircle2 className="h-3 w-3 ml-1" /> شعبه انتخابی شما
                  </Badge>
                )}
              </div>

              <CardContent className="space-y-3 p-5">
                <p className="flex items-start gap-2 text-sm text-muted-foreground">
                  <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-primary" />
                  {b.address}
                </p>
                <div className="flex flex-wrap gap-x-6 gap-y-2 text-sm text-muted-foreground">
                  <span className="flex items-center gap-1.5">
                    <Phone className="h-4 w-4 text-primary" /> {faDigits(b.phone)}
                  </span>
                  <span className="flex items-center gap-1.5">
                    <Clock className="h-4 w-4 text-primary" />
                    همه‌روزه {faDigits(b.open_time)} تا {faDigits(b.close_time)}
                  </span>
                </div>
                <div className="flex flex-wrap gap-2 text-[11px]">
                  <Badge variant="secondary" className="gap-1">
                    <Bike className="h-3 w-3" /> ارسال {toman(b.delivery_fee)} تومان
                  </Badge>
                  <Badge variant="secondary">حداقل سفارش {toman(b.min_order / 1000)} هزار تومان</Badge>
                  <Badge variant="secondary" className="bg-emerald-100 text-emerald-700">
                    ارسال رایگان بالای {toman(b.free_delivery_over / 1000)} هزار
                  </Badge>
                </div>

                <div className="flex gap-2 pt-1">
                  <Button
                    className="flex-1"
                    onClick={() => {
                      setBranch(b.id);
                      navigate("/menu");
                    }}
                  >
                    سفارش از این شعبه
                  </Button>
                  {b.lat && b.lng && (
                    <Button
                      variant="outline"
                      onClick={() =>
                        window.open(
                          `https://www.google.com/maps/dir/?api=1&destination=${b.lat},${b.lng}`,
                          "_blank"
                        )
                      }
                    >
                      <Navigation className="h-4 w-4 ml-1" />
                      مسیریابی
                    </Button>
                  )}
                </div>
              </CardContent>
            </Card>
          );
        })}
      </div>
    </div>
  );
}
