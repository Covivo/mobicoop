import React from 'react';
import { List, Datagrid, TextField, ShowButton, EditButton } from 'react-admin';

export const RoleList = (props) => (
    <List {...props} title="Roles" perPage={ 30 }>
        <Datagrid>
            <TextField source="id" label="ID"/>
            <TextField source="title" label="Titre"/>
            <TextField source="name" label="Nom"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);