import React from 'react';
import { makeStyles } from '@material-ui/core/styles';
import AvatarField from './AvatarField';

const useStyles = makeStyles(theme => ({
    root: {
        display: 'flex',
        flexWrap: 'nowrap',
        alignItems: 'center',
    },
    avatar: {
        marginRight: theme.spacing(1),
    },
}));

/* Expected User record :
@id: "/users/4"
@type: "User"
id: 4
givenName: "Hervé"
shortFamilyName: "F."
avatars: ["/images/avatarsDefault/square_100.svg", "/images/avatarsDefault/square_250.svg"]
*/

const FullNameField = ({ record, size }) => {
    const classes = useStyles();
    return record ? (
        <div className={classes.root}>
            <AvatarField
                className={classes.avatar}
                record={record}
                size={size}
            />
            {record.givenName} {record.familyName || record.shortFamilyName}
        </div>
    ) : null;
};


export default FullNameField;