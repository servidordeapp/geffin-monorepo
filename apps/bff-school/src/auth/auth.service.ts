import { HttpService } from '@nestjs/axios';
import { HttpException, Injectable } from '@nestjs/common';
import { AxiosError } from 'axios';
import { firstValueFrom } from 'rxjs';

const API_BASE = process.env.API_URL ?? 'http://api:8000';

@Injectable()
export class AuthService {
  constructor(private readonly http: HttpService) {}

  async login(body: { email: string; password: string }) {
    try {
      const { data } = await firstValueFrom(
        this.http.post(`${API_BASE}/api/v1/admin/auth/login`, body),
      );
      return data;
    } catch (err) {
      this.rethrow(err);
    }
  }

  async logout(token: string) {
    try {
      const { data } = await firstValueFrom(
        this.http.post(
          `${API_BASE}/api/v1/admin/auth/logout`,
          {},
          { headers: { Authorization: `Bearer ${token}` } },
        ),
      );
      return data;
    } catch (err) {
      this.rethrow(err);
    }
  }

  async forgotPassword(body: { email: string }) {
    try {
      const { data } = await firstValueFrom(
        this.http.post(`${API_BASE}/api/v1/admin/auth/forgot-password`, body),
      );
      return data;
    } catch (err) {
      this.rethrow(err);
    }
  }

  async resetPassword(body: { token: string; email: string; password: string; password_confirmation: string }) {
    try {
      const { data } = await firstValueFrom(
        this.http.post(`${API_BASE}/api/v1/admin/auth/reset-password`, body),
      );
      return data;
    } catch (err) {
      this.rethrow(err);
    }
  }

  async verifyEmail(id: string, hash: string, query: Record<string, string>) {
    const params = new URLSearchParams(query).toString();
    try {
      const { data } = await firstValueFrom(
        this.http.get(`${API_BASE}/api/v1/admin/auth/verify-email/${id}/${hash}?${params}`),
      );
      return data;
    } catch (err) {
      this.rethrow(err);
    }
  }

  async resendVerification(body: { email: string }) {
    try {
      const { data } = await firstValueFrom(
        this.http.post(`${API_BASE}/api/v1/admin/auth/resend-verification`, body),
      );
      return data;
    } catch (err) {
      this.rethrow(err);
    }
  }

  private rethrow(err: unknown): never {
    if (err instanceof AxiosError && err.response) {
      throw new HttpException(err.response.data, err.response.status);
    }
    throw err;
  }
}
