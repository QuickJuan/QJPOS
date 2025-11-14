export const useModalStyle = () => {
    const getModalStyle = (itemCount: number) => {
        if (itemCount <= 3) {
            return { width: "30rem", maxHeight: "80vh" };
        } else if (itemCount <= 6) {
            return { width: "40rem", maxHeight: "85vh" };
        } else {
            return { width: "50rem", maxHeight: "90vh" };
        }
    };

    return {
        getModalStyle,
    };
};
