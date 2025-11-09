# Migrating from data and domain to module architecture

### Existing modules:

-   User (encompasses auth and profile)
-   Document (encompasses documents, trash and sharing)

### Rules

1. The Http folder stays out of the Modular architecture, because it could call uppon one or more modules
2. Use hexagonal architecture (ports and adapters) when applicable
3. Each module has public domain classes (DTO folder and Actions folder)
4. Each module has Data classes, that involve data sources (e.g. Eloquent Models, Ports and Adapters for external services)
5. Each module has an optional Logic folder with classes containing potential duplicate logic that may show up (for example, generating a register token in the Auth module will be done by the EmailLogin and GoogleLogin action)

### Folder structure

```
/[MODULE_NAME]
|--- Domain
    |--- Actions
    |--- DTO
|--- Data
    |--- Adapters
    |--- Ports
    |--- Models
|--- Logic
```
