// Filament 5.10 exposes dropdown entries as menuitem rather than button or link.
const namedRole = name => ({ name, exact: true });

export function getDropdownAction(scope, name) {
    const matcher = namedRole(name);

    return scope.getByRole('menuitem', matcher).or(scope.getByRole('button', matcher));
}

export function getDropdownNavigationLink(scope, name) {
    const matcher = namedRole(name);

    return scope.getByRole('menuitem', matcher).or(scope.getByRole('link', matcher));
}
