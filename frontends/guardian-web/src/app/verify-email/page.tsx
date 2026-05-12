'use client';

import { useEffect, useState } from 'react';

export default function VerifyEmailPage() {
  const [status, setStatus] = useState<'loading' | 'success' | 'error'>('loading');
  const [message, setMessage] = useState('');
  const [errorCode, setErrorCode] = useState<string | null>(null);

  useEffect(() => {
    const url = new URL(window.location.href);
    const extra = url.pathname.replace('/verify-email', '');
    const verifyUrl = `/api/auth/verify-email${extra}${url.search}`;

    fetch(verifyUrl)
      .then((res) => res.json())
      .then((json) => {
        if (json.data) {
          setStatus('success');
          setMessage('Email verified! You can now log in.');
        } else {
          setStatus('error');
          setErrorCode(json.errors?.[0]?.code ?? null);
          setMessage(json.errors?.[0]?.message ?? 'Verification failed.');
        }
      })
      .catch(() => {
        setStatus('error');
        setMessage('Network error. Please try again.');
      });
  }, []);

  if (status === 'loading') return <p>Verifying...</p>;

  return (
    <main>
      <p role="alert">{message}</p>
      {status === 'success' && <a href="/login">Go to login</a>}
      {status === 'error' && (
        <>
          <a href="/login">Back to login</a>
          {errorCode === 'LINK_EXPIRED' && (
            <p>
              <a href="/resend-verification">Request a new verification email</a>
            </p>
          )}
        </>
      )}
    </main>
  );
}
