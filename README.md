OctavePasswordBundle
==========================

## Configuration
Bundle configuration in config/packages/octave_password.yaml:
```yaml
octave_password:
  redirect_route_name: octave.password.change.password  # Route to redirect for password change
  send_email: true                                      # Enable send email
  mailer_class: App\Service\Mailer                     # Mailer service class
  user_class: App\Entity\User                          # User entity class
  ask_current_password: true                           # Require current password for changes
  reset_password:
    token_lifetime: 60                               # Reset token lifetime in minutes
    resend_interval: 15                              # Minutes between reset requests
  password:
    min_length: 10                                   # Minimum password length
    max_length: 25                                  # Maximum password length
    complexity_level: easy                           # Password complexity level
    expiration_days: 0                               # Password expiration period
    keep_history: no                              # Enable password history
    history_count: 0                                 # Number of passwords to keep
```

### Password Policy Parameters
1. min_length (default: 10)
- Minimum required length for passwords
2. max_length (default: 25)
- Maximum required length for passwords
3. complexity_level (default: easy)
- easy: No specific character requirements
- medium: Must contain mix of letters and numbers
- high: Must contain uppercase letters, lowercase letters, numbers, and special characters
4. expiration_days (default: 0)
- Number of days until password expires
- 0 means passwords never expire
5. keep_history (default: no)
- Whether to track password history
6. history_count (default: 0)
- Number of passwords checked in history