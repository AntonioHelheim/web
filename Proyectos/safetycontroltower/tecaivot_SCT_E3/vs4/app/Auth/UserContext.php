<?php
final class SctUserContext
{
    public string $userId;
    public int $companyId;
    public array $roles;
    public string $primaryRole;
    public int $level;
    public bool $globalScope;
    public array $profile;

    public function __construct(
        string $userId,
        int $companyId,
        array $roles,
        string $primaryRole,
        int $level,
        bool $globalScope,
        array $profile
    ) {
        $this->userId = $userId;
        $this->companyId = $companyId;
        $this->roles = $roles;
        $this->primaryRole = $primaryRole;
        $this->level = $level;
        $this->globalScope = $globalScope;
        $this->profile = $profile;
    }

    public function toArray(): array
    {
        return [
            'session_user_id' => $this->userId,
            'company_id' => $this->companyId,
            'actor_roles' => $this->roles,
            'primary_role' => $this->primaryRole,
            'actor_level' => $this->level,
            'actor_label' => SctRolePolicy::label($this->primaryRole),
            'is_global_admin' => $this->globalScope,
            'profile' => $this->profile,
        ];
    }
}
