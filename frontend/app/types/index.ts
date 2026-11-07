export interface ApiResponse<T = any> {
  success: boolean;
  status: number;
  message: string;
  data: T;
  meta: PaginationMeta | null;
  errors: Record<string, string[]> | null;
}

export interface PaginationMeta {
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'viewer';
  created_at?: string;
}

export interface Category {
  id: number;
  name: string;
  chart_of_accounts_count?: number;
  created_at?: string;
  updated_at?: string;
}

export interface ChartOfAccount {
  id: number;
  code: string;
  name: string;
  category_id: number;
  category?: Category;
  transactions_count?: number;
  created_at?: string;
  updated_at?: string;
}

export interface Transaction {
  id: number;
  date: string;
  coa_id: number;
  description: string;
  debit: number;
  credit: number;
  coa?: ChartOfAccount;
  created_at?: string;
  updated_at?: string;
}

export interface DashboardSummary {
  month: string;
  start_date: string;
  end_date: string;
  total_income: number;
  total_expense: number;
  net_income: number;
  credit_count: number;
  debit_count: number;
}

export interface ProfitLossReport {
  period: {
    start_month: string;
    end_month: string;
    start_date: string;
    end_date: string;
  };
  months: string[];
  income_categories: {
    category_id: number;
    category_name: string;
    type: 'income';
    monthly: Record<string, number>;
    total: number;
  }[];
  expense_categories: {
    category_id: number;
    category_name: string;
    type: 'expense';
    monthly: Record<string, number>;
    total: number;
  }[];
  totals_by_month: Record<string, {
    total_income: number;
    total_expense: number;
    net_income: number;
  }>;
  summary: {
    total_income: number;
    total_expense: number;
    net_income: number;
  };
}

