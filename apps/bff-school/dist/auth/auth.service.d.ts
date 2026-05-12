import { HttpService } from '@nestjs/axios';
export declare class AuthService {
    private readonly http;
    constructor(http: HttpService);
    login(body: {
        email: string;
        password: string;
    }): Promise<any>;
    logout(token: string): Promise<any>;
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
