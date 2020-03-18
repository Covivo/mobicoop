import React from 'react';
import Avatar from '@material-ui/core/Avatar';

const AvatarField = ({ record, size = '25', className }) => {
    if (!record) return null
    return (
    record && record.avatars ? (
        <Avatar
            src={`${record.avatars[0]}`}
            style={{ width: parseInt(size, 10), height: parseInt(size, 10) }}
            className={className}
        />
        ) : <Avatar
            style={{ width: parseInt(size, 10), height: parseInt(size, 10) }}
            className={className}
            >{record.shortFamilyName ? record.shortFamilyName[0] : ""} </Avatar>
    )
}

export default AvatarField;