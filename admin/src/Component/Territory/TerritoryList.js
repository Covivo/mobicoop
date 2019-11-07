import React from 'react';
import { 
    List,
    Datagrid,
    TextField, 
    ShowButton, EditButton
} from 'react-admin';

export const TerritoryList = (props) => (
    <List {...props} title="Territoires > liste" perPage={ 25 }>
        <Datagrid>
            <TextField source="originId" label="ID" sortBy="id"/>
            <TextField source="name" label="Nom"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);