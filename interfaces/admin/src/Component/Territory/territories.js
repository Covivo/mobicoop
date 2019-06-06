import React from 'react';
import { 
    List,
    Datagrid,
    TextField,
    ShowButton, EditButton,
} from 'react-admin';

// List
export const TerritoryList = (props) => (
    <List {...props} title="Territoires > liste" perPage={ 30 }>
        <Datagrid>
            <TextField source="originId" label="ID"/>
            <TextField source="name" label="Nom"/>
            <ShowButton />
            <EditButton />
        </Datagrid>
    </List>
);