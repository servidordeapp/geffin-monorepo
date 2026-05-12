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
const rxjs_1 = require("rxjs");
const API_BASE = process.env.API_URL ?? 'http://api:8000';
let AuthService = class AuthService {
    constructor(http) {
        this.http = http;
    }
    async login(body) {
        const { data } = await (0, rxjs_1.firstValueFrom)(this.http.post(`${API_BASE}/api/v1/admin/auth/login`, body));
        return data;
    }
    async logout(token) {
        const { data } = await (0, rxjs_1.firstValueFrom)(this.http.post(`${API_BASE}/api/v1/admin/auth/logout`, {}, { headers: { Authorization: `Bearer ${token}` } }));
        return data;
    }
    async forgotPassword(body) {
        const { data } = await (0, rxjs_1.firstValueFrom)(this.http.post(`${API_BASE}/api/v1/admin/auth/forgot-password`, body));
        return data;
    }
    async resetPassword(body) {
        const { data } = await (0, rxjs_1.firstValueFrom)(this.http.post(`${API_BASE}/api/v1/admin/auth/reset-password`, body));
        return data;
    }
};
exports.AuthService = AuthService;
exports.AuthService = AuthService = __decorate([
    (0, common_1.Injectable)(),
    __metadata("design:paramtypes", [axios_1.HttpService])
], AuthService);
//# sourceMappingURL=auth.service.js.map