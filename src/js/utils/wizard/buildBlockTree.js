export function buildBlockTree(flatBlocks) {
    const topLevel = flatBlocks.filter(b => !b.parent_instance_id);
    const childrenOf = (parentId) =>
        flatBlocks
            .filter(b => b.parent_instance_id === parentId)
            .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));

    const addChildren = (block) => ({
        ...block,
        _children: childrenOf(block.id).map(addChildren),
    });

    return topLevel
        .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0))
        .map(addChildren);
}
