import React from 'react';
import isAuthorized from '../../Auth/permissions'
import { defaultExporter,Button,useTranslate } from 'react-admin';
//import bcrypt from 'bcryptjs';

import {
    List,
    Datagrid,
    TextInput, SelectInput, ReferenceInput, BooleanInput,
    TextField, EmailField, DateField,
    EditButton, BulkDeleteButton,BooleanField,
    Filter
} from 'react-admin';

import EmailComposeButton from '../Email/EmailComposeButton';
import ResetButton from '../Utilities/ResetButton';
import MyDatagrid from '../Utilities/MyDatagrid';

const UserList = (props) => {

  const translate = useTranslate();

  const UserBulkActionButtons = props => (
      <>
          {isAuthorized("mass_create") && <EmailComposeButton label="Email" {...props} /> }
          <ResetButton label="Reset email" {...props} />
          {/* default bulk delete action */}
            {/* <BulkDeleteButton {...props} /> */}
      </>
  );
  const UserFilter = (props) => (
      <Filter {...props}>
          <TextInput source="givenName" label={translate('custom.label.user.givenName')} />
          <TextInput source="familyName" label={translate('custom.label.user.familyName')} alwaysOn />
          <TextInput source="email" label={translate('custom.label.user.email')} alwaysOn />
          <BooleanInput source="solidary" label={translate('custom.label.user.solidary')} allowEmpty={false} defaultValue={true} />
          <ReferenceInput
              source="homeAddressODTerritory"
              label={translate('custom.label.user.territory')}
              reference="territories"
              allowEmpty={false}
              resettable>
              <SelectInput optionText="name" optionValue="id"/>
          </ReferenceInput>
      </Filter>
  );

  return (
    <List {...props}
          title="Utilisateurs > liste"
          perPage={ 25 }
          filters={<UserFilter />}
          sort={{ field: 'id', order: 'ASC' }}
          bulkActionButtons={<UserBulkActionButtons />}
          //exporter={isAuthorized("right_user_assign") ? defaultExporter : false}
          exporter={false}
          hasCreate={isAuthorized('user_create')}
    >
        <MyDatagrid  rowClick="show">
            <TextField source="originId" label={translate('custom.label.user.id')} sortBy="id"/>
            <TextField source="givenName" label={translate('custom.label.user.givenName')}  />
            <TextField source="familyName" label={translate('custom.label.user.familyName')} />
            <EmailField source="email" label={translate('custom.label.user.email')} />
            <BooleanField source="newsSubscription" label={translate('custom.label.user.accepteEmail')}/>
            <DateField source="createdDate" label={translate('custom.label.user.createdDate')}/>
            {isAuthorized("user_update") &&
            <EditButton />
            }
        </MyDatagrid>
    </List>
  )
};

export default UserList;
