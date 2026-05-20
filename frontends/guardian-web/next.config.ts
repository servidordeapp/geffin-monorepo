import type { NextConfig } from 'next';
import path from 'path';

const fixturesPath = path.resolve(__dirname, '../../specs/002-initial-screens/fixtures');

const nextConfig: NextConfig = {
  transpilePackages: ['@gfn/design-system'],
  turbopack: {
    resolveAlias: {
      '@fixtures': fixturesPath,
    },
  },
  webpack: (config) => {
    config.resolve.modules = ['node_modules', path.resolve(__dirname, '../../node_modules')];
    config.resolve.alias = {
      ...config.resolve.alias,
      '@fixtures': fixturesPath,
    };
    return config;
  },
};

export default nextConfig;
