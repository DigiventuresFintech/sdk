export type Environment = 'qa' | 'staging' | 'production';

export interface DigiventuresConfig {
  applicationId: string;
  secret: string;
  environment: Environment;
  timeout?: number; // in milliseconds, default: 10000
  maxRetries?: number; // default: 3
}

export interface AuthToken {
  token: string;
  expiration: string;
  api: {
    version: string;
  };
}

export interface Legajo {
  _id: string;
  name: string;
  email: string;
  idNumber: string;
  referenceCode: string;
  linkLandingNext: string;
  linkRecover: string;
  createdAt: string;
  updatedAt: string;
}

export interface LegajoCreateData {
  firstname?: string;
  lastname?: string;
  email?: string;
  idNumber?: string;
  name?: string;
}

export interface LegajoUpdateData {
  vouchers?: Record<string, any>;
  [key: string]: any;
}

export interface FileResponse {
  file: string; // base64 encoded
} 