import React from 'react';
import { List, Datagrid, TextField, BooleanField, DateField, ShowButton, EditButton } from 'react-admin';

export const CommunityList = (props) => (
    <List {...props} title="Communities" perPage={ 30 }>
        <Datagrid>
            <TextField source="originId" label="ID"/>
            <TextField source="name" label="Nom"/>
            <BooleanField source="private" label="Privée" />
            <TextField source="description" label="Description"/>
            <DateField source="createdDate" label="Date de création"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);