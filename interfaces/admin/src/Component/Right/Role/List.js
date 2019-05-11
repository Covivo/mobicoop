import React from 'react';
import { List, Datagrid, TextField, ReferenceField, ShowButton, EditButton } from 'react-admin';

export const RoleList = (props) => (
    <List {...props} title="Roles" perPage={ 30 }>
        <Datagrid>
            <TextField source="id" label="ID"/>
            <TextField source="title" label="Titre"/>
            <TextField source="name" label="Nom"/>
            <ReferenceField label="Rôle parent" source="parent" reference="roles" allowEmpty>
                <TextField source="title" />
            </ReferenceField>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);