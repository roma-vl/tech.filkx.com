export interface Invoice {
  id: number;
  invoiceNumber: string;
  orderNumber: string;
  customerName: string;
  customerEmail: string;
  amount: number;
  status: "paid" | "unpaid" | "void";
  dueDate: string;
  issuedDate: string;
  paidDate?: string | null;
}

export interface LedgerEntry {
  id: number;
  transactionId: string;
  type: "income" | "expense";
  category: string;
  amount: number;
  description: string;
  reference?: string;
  createdAt: string;
}
