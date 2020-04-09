import React from 'react';

import TableCell from '@material-ui/core/TableCell';
import TableRow from '@material-ui/core/TableRow';
import Checkbox from '@material-ui/core/Checkbox';
import {
    List,
    Datagrid,DatagridBody,
    TextInput,  TextField
} from 'react-admin';


  const MyDatagridRow = ({ record, resource, id, onToggleItem, children, selected, basePath }) => (


    <TableRow key={id} hover={true}>
        {/* first column: selection checkbox */}
        <TableCell padding="none">
            <Checkbox
                checked={selected}
                onClick={() => onToggleItem(id)}
            />
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

export default MyDatagrid;
