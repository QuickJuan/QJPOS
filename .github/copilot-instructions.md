# Log in process

-   User should be able to select branch where they want to log in.
-   User should be able to reveal and hide password.
-   After login, user should be redirected Home Navigation instead of Dasboard.
    -   Home page navigation should have 4 buttons
        -   Dasboard
        -   Start Cashiering
        -   Table Ordering
        -   Clock in/out for attendance.
-   User should be able to log out.

# clock in / out process

-   User should be able to clock in and clock out.
-   Create page for clock in and clock out.
-   On attendance page, user should be able to input their employee code and click clock in or clock out button.
-   System should be able to detect if user is clocking in or clocking out. from attendance table system will check if the user id exists in the attendance table that does not have actual_timeout.
    -   If user id exists, system will update the actual_timeout column with current time.
    -   If user id does not exist, system will create a new record in the attendance table and will put the current time in actual_time_in column.
-   Enable camera access for attendance.
    -   when the user click the Clock In/Out button, the system should access the camera and take a photo of the user.
    -   there will be 1 button for Clock In and Clock Out.
    -   System will preview the photo before saving it. when the user click the clock in/out button, the system will take a photo of the user and will show the preview of the photo.
    -   Confirmation dialog will be shown to the user before saving the photo and the attendance record.
    -   use the spatie media library to store the photo. there will be 2 photo associated with the attendance record.
        -   photo for clock in
        -   photo for clock out
-   system will need to check if the user is associated with the branch where they are clocking in/out.
    -   if the user is not associated with the branch, system will show an error message and will not allow the user to clock in/out.
