import { HttpService } from '@nestjs/axios';
import { Injectable } from '@nestjs/common';
import { firstValueFrom } from 'rxjs';

const API_BASE = process.env.API_URL ?? 'http://api:8000';

@Injectable()
export class AuthService {
  constructor(private readonly http: HttpService) {}

  async login(body: { email: string; password: string }) {
    const { data } = await firstValueFrom(
      this.http.post(`${API_BASE}/api/v1/admin/auth/login`, body),
    );
    return data;
  }

  async logout(token: string) {
    const { data } = await firstValueFrom(
      this.http.post(
        `${API_BASE}/api/v1/admin/auth/logout`,
        {},
        { headers: { Authorization: `Bearer ${token}` } },
      ),
    );
    return data;
  }

  async forgotPassword(body: { email: string }) {
    const { data } = await firstValueFrom(
      this.http.post(`${API_BASE}/api/v1/admin/auth/forgot-password`, body),
    );
    return data;
  }

  async resetPassword(body: { token: string; email: string; password: string; password_confirmation: string }) {
    const { data } = await firstValueFrom(
      this.http.post(`${API_BASE}/api/v1/admin/auth/reset-password`, body),
    );
    return data;
  }
}
