import React from 'react';
import { List, Datagrid, TextField, EmailField, DateField, ShowButton, EditButton } from 'react-admin';
// import { CustomPagination } from '../Pagination/CustomPagination';

export const UserList = (props) => (
    <List {...props} title="Users" perPage={ 30 }>
        <Datagrid>
            <TextField source="id" label="ID"/>
            <TextField source="givenName" label="Prénom"/>
            <TextField source="familyName" label="Nom"/>
            <EmailField source="email" label="Email" />
            <DateField source="createdDate" label="Date de création"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);