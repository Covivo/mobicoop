import React, {useEffect, useState} from 'react';
import {isAuthorized} from '../Utilities/authorization';
import { defaultExporter } from 'react-admin';
//import bcrypt from 'bcryptjs';

import { 
    List,
    Datagrid,
    TextInput, SelectInput, ReferenceInput, BooleanInput,
    TextField, EmailField, DateField, 
    EditButton, BulkDeleteButton,DatagridBody,
    Filter,BooleanField
} from 'react-admin';

import TableCell from '@material-ui/core/TableCell';
import TableRow from '@material-ui/core/TableRow';
import Checkbox from '@material-ui/core/Checkbox';

import EmailComposeButton from '../Email/EmailComposeButton';

const UserList = props => {
    const [countChecked, setCountChecked] = useState(0);
    const UserBulkActionButtons = props => (
        <>
            <EmailComposeButton label="Email" {...props} countChecked={countChecked} />
            {/* default bulk delete action */}
            <BulkDeleteButton {...props} />
        </>
    );

        const handledCheckCLick = (event,news) => {
            if (!news) setCountChecked(event.target.checked ? countChecked + 1 : countChecked - 1);
        };


    const MyDatagridRow = ({ record, resource, id, onToggleItem, children, selected, basePath }) => (
        <TableRow key={id}>
            {/* first column: selection checkbox */}
            <TableCell padding="none">
                {<Checkbox
                    checked={selected}
                    onClick={() => { onToggleItem(id);}}
                    onChange={(event) => handledCheckCLick(event,record.newsSubscription)}
                />}
            </TableCell>
            {/* data columns based on children */}
            {React.Children.map(children, field => (
                <TableCell key={`${id}-${field.props.source}`}>
                    {React.cloneElement(field, {
                        record,
                        basePath,
                        resource,
                    })}
                </TableCell>
            ))}
        </TableRow>
    )

    const MyDatagridBody = props => <DatagridBody {...props} row={<MyDatagridRow />} />;
    const MyDatagrid = props => <Datagrid {...props} body={<MyDatagridBody />} />;

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
    return (
        <List {...props}
                title="Utilisateurs > liste"
                perPage={ 25 }
                filters={<UserFilter />}
                sort={{ field: 'id', order: 'ASC' }}
                bulkActionButtons={<UserBulkActionButtons />}
                exporter={isAuthorized("right_user_assign") ? defaultExporter : false}
        >
            <MyDatagrid rowClick="show">
                <TextField source="originId" label="ID" sortBy="id"/>
                <TextField source="givenName" label="Prénom"/>
                <TextField source="familyName" label="Nom"/>
                <EmailField source="email" label="Email" />
                <BooleanField source="newsSubscription" label="Accepte les emails"/>
                <DateField source="createdDate" label="Date de création"/>
                {isAuthorized("user_update") &&
                    <EditButton />
                }
            </MyDatagrid>
        </List>
    );
}

export default UserList;