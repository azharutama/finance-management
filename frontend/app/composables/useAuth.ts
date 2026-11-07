import type { User, ApiResponse } from '~/types';

export const useAuth = () => {
  const token = useCookie<string | null>('auth_token', { maxAge: 60 * 60 * 24 * 7 });
  const user = useState<User | null>('auth_user', () => null);
  const config = useRuntimeConfig();

  const setAuth = (newUser: User, newToken: string) => {
    token.value = newToken;
    user.value = newUser;
  };

  const clearAuth = () => {
    token.value = null;
    user.value = null;
  };

  const fetchUser = async () => {
    if (!token.value) {
      user.value = null;
      return null;
    }
    try {
      const res = await $fetch<ApiResponse<User>>(`${config.public.apiBaseUrl}/auth/me`, {
        headers: {
          Authorization: `Bearer ${token.value}`,
          Accept: 'application/json',
        },
      });
      if (res && res.success) {
        user.value = res.data;
        return res.data;
      }
    } catch (e) {
      clearAuth();
    }
    return null;
  };

  const logout = async () => {
    if (token.value) {
      try {
        await $fetch(`${config.public.apiBaseUrl}/auth/logout`, {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token.value}`,
            Accept: 'application/json',
          },
        });
      } catch (e) {
        // ignore logout errors
      }
    }
    clearAuth();
    navigateTo('/login');
  };

  return {
    token,
    user,
    setAuth,
    clearAuth,
    fetchUser,
    logout,
    isAuthenticated: computed(() => !!token.value),
  };
};

