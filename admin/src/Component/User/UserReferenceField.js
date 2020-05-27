
import React from 'react';
import { ReferenceField } from 'react-admin';

import FullNameField from './FullNameField';

const UserReferenceField = props => {

  //We test the roles in localStorage, and we check if user have role admin or super admin
  //If he dont : he's a community manager, we dont show the detail of user
  let stateLink= true;
  if (localStorage && localStorage.roles){

      const roles = Array.isArray( localStorage.roles) ?  localStorage.roles.split(',') :  localStorage.roles;
          if (!roles.includes('ROLE_SUPER_ADMIN') && !roles.includes('ROLE_ADMIN')){
              stateLink = false;
        }
      }
    return (
          <ReferenceField source="user" reference="users" {...props} link={stateLink}>
            <FullNameField />
        </ReferenceField>
      );
};

UserReferenceField.defaultProps = {
    source: 'user',
    addLabel: true,
};

export default UserReferenceField;
