document.addEventListener('DOMContentLoaded', function () {
  // Both admin and staff can access settings
  Auth.initPage({
    requireAuth: true,
    allowedRoles: ['admin', 'staff'],
    initSidebar: true
  });

  console.log('Settings loaded for:', Auth.getUserRole());

  const user = Auth.getCurrentUser();
  if (!user) return;

  //update the top sidebar user info
  Auth.updateUserInfo();

  // Update the profile picture section (initial) and the personal information
  const settingsInitial = document.querySelector('.settings-initial');
  if (settingsInitial) settingsInitial.textContent = user.avatar;
  const [firstNameInput, lastNameInput] = document.querySelectorAll('.settings-info input[type="text"]');
  const emailInput = document.querySelector('.settings-info input[type="email"]');
  const roleSelect = document.getElementById('role');


  if (firstNameInput && lastNameInput) {
    const nameParts = user.name.split(' ');
    firstNameInput.value = nameParts[0] || '';
    lastNameInput.value = nameParts.slice(1).join(' ') || '';
  }

  if (emailInput) emailInput.value = user.email;
  if (roleSelect) {
    roleSelect.value = user.role === 'admin' ? 'Administrator' : 'Staff';
  }

  console.log(`Settings loaded for ${user.name} (${user.role})`);
});

