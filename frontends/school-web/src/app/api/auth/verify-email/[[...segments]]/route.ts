import { NextRequest, NextResponse } from 'next/server';

const BFF_URL = process.env.BFF_URL ?? 'http://localhost:3002';

export async function GET(
  req: NextRequest,
  { params }: { params: Promise<{ segments?: string[] }> },
) {
  const { segments } = await params;
  const extra = segments ? `/${segments.join('/')}` : '';
  const search = req.nextUrl.search;
  const res = await fetch(`${BFF_URL}/auth/verify-email${extra}${search}`);
  const data = await res.json();
  return NextResponse.json(data, { status: res.status });
}
