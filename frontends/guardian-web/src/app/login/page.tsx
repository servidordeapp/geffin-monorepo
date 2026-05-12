'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const res = await fetch('/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password }),
      });

      const json = await res.json();

      if (!res.ok) {
        const code = json?.errors?.[0]?.code;
        if (code === 'EMAIL_NOT_VERIFIED') {
          setError('EMAIL_NOT_VERIFIED');
        } else {
          setError(json?.errors?.[0]?.message ?? 'Login failed.');
        }
        return;
      }

      localStorage.setItem('auth_token', json.data.token);
      router.push('/dashboard');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <main>
      <h1>Guardian Login</h1>
      <form onSubmit={handleSubmit}>
        <div>
          <label htmlFor="email">Email</label>
          <input
            id="email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            required
            aria-required="true"
          />
        </div>
        <div>
          <label htmlFor="password">Password</label>
          <input
            id="password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            aria-required="true"
          />
        </div>
        {error === 'EMAIL_NOT_VERIFIED' && (
          <p role="alert">
            Please verify your email first.{' '}
            <a href="/resend-verification">Resend verification email</a>
          </p>
        )}
        {error && error !== 'EMAIL_NOT_VERIFIED' && (
          <p role="alert">{error}</p>
        )}
        <button type="submit" disabled={loading}>
          {loading ? 'Signing in...' : 'Sign in'}
        </button>
      </form>
      <p>
        <a href="/forgot-password">Forgot password?</a>
      </p>
    </main>
  );
}
