import { useEffect, useState } from 'react';
import { Button, StyleSheet, Text, View } from 'react-native';

const BFF_URL = process.env.EXPO_PUBLIC_BFF_URL ?? 'http://localhost:3001';

export default function VerifyEmailScreen({ route, navigation }: { route: any; navigation: any }) {
  const { id, hash, expires, signature } = route.params ?? {};
  const [status, setStatus] = useState<'loading' | 'success' | 'error'>('loading');
  const [message, setMessage] = useState('');

  useEffect(() => {
    fetch(`${BFF_URL}/auth/verify-email/${id}/${hash}?expires=${expires}&signature=${signature}`)
      .then((res) => res.json())
      .then((json) => {
        if (json.data) {
          setStatus('success');
          setMessage('Email verified! You can now log in.');
        } else {
          setStatus('error');
          setMessage(json.errors?.[0]?.message ?? 'Verification failed.');
        }
      })
      .catch(() => {
        setStatus('error');
        setMessage('Network error. Please try again.');
      });
  }, [id, hash, expires, signature]);

  return (
    <View style={styles.container}>
      <Text>{status === 'loading' ? 'Verifying...' : message}</Text>
      {status !== 'loading' && (
        <Button title="Go to Login" onPress={() => navigation.navigate('Login')} />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, padding: 24, justifyContent: 'center', alignItems: 'center' },
});
