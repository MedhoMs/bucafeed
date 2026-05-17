export const ABOUT_US_TIMELINE = [
    {
        id: 1,
        key: "origin",
        icon: "origin",
        color: "#326465"
    },
    {
        id: 2,
        key: "tools",
        icon: "tools",
        color: "#2a4a5a"
    },
    {
        id: 3,
        key: "community",
        icon: "community",
        color: "#326465"
    },
    {
        id: 4,
        key: "future",
        icon: "future",
        color: "#2a4a5a"
    }
];

export const PROJECT_TEAM = [
    {
        name: "Antonio Morera Marrero",
        github: "https://github.com/AntonioMorera"
    },
    {
        name: "Daniel Bucaloiu Morales",
        github: "https://github.com/danielbucaloiu"
    },
    {
        name: "Jason Camila Sotto",
        github: "https://github.com/jasoncs-16"
    }
].map(member => ({
    ...member,
    avatar: member.github + ".png"
}));
