export interface User {
    id: number;
    name: string;
    email: string;
    phone?: string;
    avatar?: string;
    email_verified_at?: string;
    roles?: { name: string }[];
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    flash?: {
        success?: string;
        error?: string;
    };
};
