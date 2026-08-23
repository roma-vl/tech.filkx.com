export default {
  title: "Broadcast Notifications",
  description: "Send manual notifications to all users or specific groups.",
  success_queued: "Notification queued successfully!",
  form: {
    title: "Title",
    message: "Message",
    type: "Type",
    recipients: "Recipients",
    user_ids: "User IDs (comma separated)",
    action_url: "Action URL (Optional)",
    submit: "Send Broadcast",
    placeholders: {
      title: "e.g. System Maintenance",
      message: "Enter notification content...",
      user_ids: "e.g. 1, 5, 23",
      action_url: "https://...",
    },
  },
  types: {
    info: "Info",
    success: "Success",
    warning: "Warning",
    error: "Error",
    fixed: "Fixed Amount",
    percent: "Percentage",
  },
  recipients: {
    all: "All Users",
    selected: "Specific User IDs",
  },
};
