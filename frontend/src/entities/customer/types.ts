import { User } from "../user/types";

export interface Customer extends User {
  ordersCount: number;
  totalSpent: number;
  status: "active" | "suspended";
}
