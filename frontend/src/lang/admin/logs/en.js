export default {
  title: "Audit Trail",
  description: "Chronological log of administrative actions",
  search_placeholder: "Search logs by action or details...",
  refresh: "Refresh",
  loading: "Loading...",
  load_more: "Load More History",
  filter_all_domains: "All Domains",
  domains: {
    security: "Security",
    billing: "Billing",
    content: "Content",
    system: "System",
    team: "Team",
  },
  table: {
    title: "System Activity Audit Trail",
    action: "ACTION",
    id: "ID",
    user_system: "System",
  },
  filters: {
    title: "Filters",
    per_page: "Items per page",
    per_page_options: {
      20: "20 items",
      50: "50 items",
      100: "100 items",
    },
  },
  server_logs: {
    title: "Server Logs",
    select_file: "Select a log file",
    select_file_desc: "Choose a log file from the list to view its content.",
    empty_file: "This log file is empty.",
    clear_log: "Clear Log",
    refresh: "Refresh",
    clear_confirm:
      "Are you sure you want to clear this log file? This action is irreversible.",
    clear_success: "Log file cleared successfully",
    clear_error: "Failed to clear log file",
  },
};
