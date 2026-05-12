import { Body, Controller, Headers, Post } from '@nestjs/common';
import { AuthService } from './auth.service';

@Controller('auth')
export class AuthController {
  constructor(private readonly authService: AuthService) {}

  @Post('login')
  login(@Body() body: { email: string; password: string }) {
    return this.authService.login(body);
  }

  @Post('logout')
  logout(@Headers('authorization') auth: string) {
    const token = auth?.replace('Bearer ', '') ?? '';
    return this.authService.logout(token);
  }

  @Post('forgot-password')
  forgotPassword(@Body() body: { email: string }) {
    return this.authService.forgotPassword(body);
  }

  @Post('reset-password')
  resetPassword(@Body() body: { token: string; email: string; password: string; password_confirmation: string }) {
    return this.authService.resetPassword(body);
  }
}
