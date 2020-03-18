
import React from 'react';
import { ReferenceField } from 'react-admin';

import FullNameField from './FullNameField';

const UserReferenceField = props => (
    <ReferenceField source="user" reference="users" {...props}>
        <FullNameField />
    </ReferenceField>
);

UserReferenceField.defaultProps = {
    source: 'user',
    addLabel: true,
};

export default UserReferenceField;
