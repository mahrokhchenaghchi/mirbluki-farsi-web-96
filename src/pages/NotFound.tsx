import { Link } from "react-router-dom";

export default function NotFound() {
  return (
    <div className="flex min-h-[50vh] flex-col items-center justify-center text-center">
      <h1 className="text-4xl font-black">صفحه پیدا نشد</h1>
      <p className="mt-3 text-muted-foreground">این مسیر در جوما وجود ندارد.</p>
      <Link to="/" className="mt-6 text-primary">
        بازگشت
      </Link>
    </div>
  );
}
