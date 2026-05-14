import './globals.css';
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'GFN Guardian',
  description: 'Guardian portal',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="pt-BR">
      <body suppressHydrationWarning>{children}</body>
    </html>
  );
}
