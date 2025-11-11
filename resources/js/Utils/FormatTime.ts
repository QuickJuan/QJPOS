// Helper function to format time occupied
export const formatTimeOccupied = (timeIn: string) => {
    const start = new Date(timeIn);
    // Format as date and time
    const dateStr = start.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
    const timeStr = start.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    });

    return `${dateStr} ${timeStr}`;
};
