"use strict";
var __decorate = (this && this.__decorate) || function (decorators, target, key, desc) {
    var c = arguments.length, r = c < 3 ? target : desc === null ? desc = Object.getOwnPropertyDescriptor(target, key) : desc, d;
    if (typeof Reflect === "object" && typeof Reflect.decorate === "function") r = Reflect.decorate(decorators, target, key, desc);
    else for (var i = decorators.length - 1; i >= 0; i--) if (d = decorators[i]) r = (c < 3 ? d(r) : c > 3 ? d(target, key, r) : d(target, key)) || r;
    return c > 3 && r && Object.defineProperty(target, key, r), r;
};
var __metadata = (this && this.__metadata) || function (k, v) {
    if (typeof Reflect === "object" && typeof Reflect.metadata === "function") return Reflect.metadata(k, v);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.AuthService = void 0;
const axios_1 = require("@nestjs/axios");
const common_1 = require("@nestjs/common");
const axios_2 = require("axios");
const rxjs_1 = require("rxjs");
const API_BASE = process.env.API_URL ?? 'http://api:8000';
let AuthService = class AuthService {
    constructor(http) {
        this.http = http;
    }
    async login(body) {
        try {
            const { data } = await (0, rxjs_1.firstValueFrom)(this.http.post(`${API_BASE}/api/v1/guardian/auth/login`, body));
            return data;
        }
        catch (err) {
            this.rethrow(err);
        }
    }
    async logout(token) {
        try {
            const { data } = await (0, rxjs_1.firstValueFrom)(this.http.post(`${API_BASE}/api/v1/guardian/auth/logout`, {}, { headers: { Authorization: `Bearer ${token}` } }));
            return data;
        }
        catch (err) {
            this.rethrow(err);
        }
    }
    async forgotPassword(body) {
        try {
            const { data } = await (0, rxjs_1.firstValueFrom)(this.http.post(`${API_BASE}/api/v1/guardian/auth/forgot-password`, body));
            return data;
        }
        catch (err) {
            this.rethrow(err);
        }
    }
    async resetPassword(body) {
        try {
            const { data } = await (0, rxjs_1.firstValueFrom)(this.http.post(`${API_BASE}/api/v1/guardian/auth/reset-password`, body));
            return data;
        }
        catch (err) {
            this.rethrow(err);
        }
    }
    async verifyEmail(id, hash, query) {
        const params = new URLSearchParams(query).toString();
        try {
            const { data } = await (0, rxjs_1.firstValueFrom)(this.http.get(`${API_BASE}/api/v1/guardian/auth/verify-email/${id}/${hash}?${params}`));
            return data;
        }
        catch (err) {
            this.rethrow(err);
        }
    }
    async resendVerification(body) {
        try {
            const { data } = await (0, rxjs_1.firstValueFrom)(this.http.post(`${API_BASE}/api/v1/guardian/auth/resend-verification`, body));
            return data;
        }
        catch (err) {
            this.rethrow(err);
        }
    }
    rethrow(err) {
        if (err instanceof axios_2.AxiosError && err.response) {
            throw new common_1.HttpException(err.response.data, err.response.status);
        }
        throw err;
    }
};
exports.AuthService = AuthService;
exports.AuthService = AuthService = __decorate([
    (0, common_1.Injectable)(),
    __metadata("design:paramtypes", [axios_1.HttpService])
], AuthService);
//# sourceMappingURL=auth.service.js.map