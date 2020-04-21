import React , {useState } from 'react';

import TableCell from '@material-ui/core/TableCell';
import TableRow from '@material-ui/core/TableRow';
import Checkbox from '@material-ui/core/Checkbox';
import {
    List,
    Datagrid,DatagridBody,
    TextInput,  TextField
} from 'react-admin';





  const MyDatagridRow = ({ record, resource, id, onToggleItem, children, selected, basePath }) => {

    const [count, setCount] = useState(0);
    const CheckValue = ({selected,record } ) => {

        if (selected) {
          setCount(count + 1)
        }

    }

    return (
        <TableRow key={id} hover={true}>
            {/* first column: selection checkbox */}
            <TableCell padding="none">
                <Checkbox
                    checked={selected}
                    onClick={() => { onToggleItem(id);CheckValue(selected,record); } }
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
    }
const MyDatagridBody = props => <DatagridBody {...props} row={<MyDatagridRow />} />;
const MyDatagridUser = props => <Datagrid {...props} body={<MyDatagridBody />} />;

export default { MyDatagridUser,count };
