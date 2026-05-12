import { AuthService } from './auth.service';
export declare class AuthController {
    private readonly authService;
    constructor(authService: AuthService);
    login(body: {
        email: string;
        password: string;
    }): Promise<any>;
    logout(auth: string): Promise<any>;
    forgotPassword(body: {
        email: string;
    }): Promise<any>;
    resetPassword(body: {
        token: string;
        email: string;
        password: string;
        password_confirmation: string;
    }): Promise<any>;
}
