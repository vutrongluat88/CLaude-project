export interface User {
  id: number
  name: string
  email: string
  role: 'admin' | 'user'
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

export interface Paginated<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
}

export interface Stats {
  total_users: number
  admins: number
  new_this_month: number
  verified: number
}
