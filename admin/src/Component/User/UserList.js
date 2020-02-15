import React from 'react';
import {isAuthorized} from '../Utilities/authorization';
import { defaultExporter } from 'react-admin';
//import bcrypt from 'bcryptjs';

import { 
    List,
    Datagrid,
    TextInput, SelectInput, ReferenceInput, BooleanInput,
    TextField, EmailField, DateField, 
    EditButton, BulkDeleteButton,
    Filter
} from 'react-admin';

import EmailComposeButton from '../Email/EmailComposeButton';

const UserBulkActionButtons = props => (
    <>
        <EmailComposeButton label="Email" {...props} />
        {/* default bulk delete action */}
        <BulkDeleteButton {...props} />
    </>
);

const UserFilter = (props) => (
    <Filter {...props}>
        <TextInput source="givenName" label="Prénom" />
        <TextInput source="familyName" label="Nom" alwaysOn />
        <TextInput source="email" label="Email" alwaysOn />
        <BooleanInput source="solidary" label="Solidaire" allowEmpty={false} defaultValue={true} />
        <ReferenceInput 
            source="homeAddressODTerritory" 
            label="Territoire" 
            reference="territories" 
            allowEmpty={false} 
            resettable>
            <SelectInput optionText="name" optionValue="id"/>
        </ReferenceInput>
    </Filter>
);
export const UserList = (props) => (
    <List {...props} 
            title="Utilisateurs > liste" 
            perPage={ 25 } 
            filters={<UserFilter />} 
            sort={{ field: 'id', order: 'ASC' }}
            bulkActionButtons={<UserBulkActionButtons />}
            exporter={isAuthorized("right_user_assign") ? defaultExporter : false}
    >
        <Datagrid rowClick="show">
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="givenName" label="Prénom"/>
            <TextField source="familyName" label="Nom"/>
            <EmailField source="email" label="Email" />
            <DateField source="createdDate" label="Date de création"/>
            {isAuthorized("user_update") && 
                <EditButton />
            }
        </Datagrid>
    </List>
);